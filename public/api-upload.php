<?php

use Dotenv\Dotenv;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Crypt;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dsn = "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
       ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
       ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'pixiecloud_db');

try {
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode([
        'message' => 'Koneksi Database Gagal: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT); 
    exit;
}

$accessKey = $_POST['access_key'] ?? '';
$secretKey = $_POST['secret_key'] ?? '';
$file = $_FILES['file'] ?? null;

if (!$accessKey || !$secretKey || !$file) {
    header('Content-Type: application/json', true, 400);
    echo json_encode([
        'message' => 'Validasi Gagal: access_key, secret_key, dan file wajib diisi!'
    ], JSON_PRETTY_PRINT); // 🔥 Rapi ke bawah
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT cc.*, s.user_id, s.service_id 
        FROM cloud_credentials cc
        JOIN subscriptions s ON cc.subscription_id = s.id
        WHERE cc.ministack_access_key = :key 
        AND cc.status = 'Active'
        AND s.status = 'Active'
        LIMIT 1
    ");
    $stmt->execute(['key' => $accessKey]);
    $credential = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$credential) {
        header('Content-Type: application/json', true, 401);
        echo json_encode([
            'message' => 'Access Key tidak valid atau sudah tidak aktif!'
        ], JSON_PRETTY_PRINT); 
        exit;
    }

    $isValid = false;
    try {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        $decryptedSecret = Crypt::decryptString($credential['ministack_secret_key']);
        $isValid = ($decryptedSecret === $secretKey);
    } catch (\Exception $e) {
        $isValid = ($credential['ministack_secret_key'] === $secretKey);
    }

    if (!$isValid) {
        header('Content-Type: application/json', true, 401);
        echo json_encode([
            'message' => 'Secret Key salah!'
        ], JSON_PRETTY_PRINT); 
        exit;
    }

    $stmtBucket = $pdo->prepare("
        SELECT * FROM buckets WHERE user_id = :user_id LIMIT 1
    ");
    $stmtBucket->execute(['user_id' => $credential['user_id']]);
    $bucket = $stmtBucket->fetch(PDO::FETCH_ASSOC);

    if (!$bucket) {
        header('Content-Type: application/json', true, 404);
        echo json_encode([
            'message' => 'Bucket tidak ditemukan untuk user ini.'
        ], JSON_PRETTY_PRINT); 
        exit;
    }

    $targetBucketName = strtolower($bucket['bucket_name']);

    $s3 = new S3Client([
        'version' => 'latest',
        'region' => $_ENV['AWS_DEFAULT_REGION'] ?? 'us-east-1',
        'endpoint' => $_ENV['AWS_ENDPOINT'] ?? 'http://127.0.0.1:4566',
        'use_path_style_endpoint' => true,
        'credentials' => [
            'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? 'test',
            'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? 'test',
        ]
    ]);

    try {
        $s3->headBucket(['Bucket' => $targetBucketName]);
    } catch (S3Exception $e) {
        if ($e->getStatusCode() === 404 || $e->getAwsErrorCode() === 'NoSuchBucket') {
            $s3->createBucket(['Bucket' => $targetBucketName]);
            error_log("Bucket auto-created: " . $targetBucketName);
        } else {
            throw $e;
        }
    }

    $fileName = time() . '_' . basename($file['name']);

    $result = $s3->putObject([
        'Bucket' => $targetBucketName,
        'Key' => $fileName,
        'Body' => fopen($file['tmp_name'], 'r'),
        'ContentType' => $file['type'] ?? 'application/octet-stream',
    ]);

    try {
        $stmtCheck = $pdo->query("SHOW TABLES LIKE 'objects'");
        $tableExists = $stmtCheck->rowCount() > 0;
        
        if ($tableExists) {
            $stmtFile = $pdo->prepare("
                INSERT INTO objects (bucket_id, object_key, content_type, file_size_bytes, file_url, created_at) 
                VALUES (:bucket_id, :object_key, :content_type, :file_size, :file_url, NOW())
            ");
        } else {
            $stmtFile = $pdo->prepare("
                INSERT INTO files (bucket_id, file_name, file_size_bytes, mime_type, created_at) 
                VALUES (:bucket_id, :object_key, :file_size, :content_type, NOW())
            ");
        }
        
        $stmtFile->execute([
            'bucket_id' => $bucket['id'],
            'object_key' => $fileName,
            'content_type' => $file['type'] ?? 'application/octet-stream',
            'file_size' => $file['size'],
            'file_url' => $result['ObjectURL'] ?? "http://127.0.0.1:4566/{$targetBucketName}/{$fileName}",
        ]);
    } catch (PDOException $e) {
        error_log("Gagal simpan metadata: " . $e->getMessage());
    }

    $stmtLog = $pdo->prepare("
        INSERT INTO activity_logs (user_id, activity, ip_address, created_at) 
        VALUES (:user_id, :activity, :ip, NOW())
    ");
    $stmtLog->execute([
        'user_id' => $credential['user_id'],
        'activity' => "Mengunggah objek [{$fileName}] dari luar platform via API Eksternal",
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);

    header('Content-Type: application/json', true, 200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Berhasil mengunggah objek ke Vault!',
        'object_name' => $fileName,
        'size_bytes' => $file['size'],
        'url' => $result['ObjectURL'] ?? "http://127.0.0.1:4566/{$targetBucketName}/{$fileName}"
    ], JSON_PRETTY_PRINT); 

} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode([
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT); 
}