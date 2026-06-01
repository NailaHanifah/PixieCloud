<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bucket_id')->constrained('buckets')->onDelete('cascade');
            $table->string('object_key');
            $table->string('content_type');
            $table->bigInteger('file_size_bytes');
            $table->text('file_url');
            $table->json('object_metadata')->nullable(); // Untuk menyimpan data kustom dari LocalStack
            $table->timestamp('created_at')->useCurrent(); // Immutable, hanya butuh created_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objects');
    }
};