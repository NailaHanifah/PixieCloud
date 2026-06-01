<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->unique()->constrained('subscriptions')->onDelete('cascade');
            $table->string('ministack_access_key');
            $table->text('ministack_secret_key'); // Menggunakan text karena akan menampung data hasil enkripsi
            $table->enum('status', ['Active', 'Revoked'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_credentials');
    }
};