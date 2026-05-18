<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            // Foreign Key terhubung ke tabel users dengan cascade delete
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('plan_name', ['Pixie Dust Pouch', 'Grove Plan', 'Dragon’s Hoard Plan']);
            $table->integer('max_buckets');
            $table->integer('max_storage_mb');
            $table->enum('status', ['Active', 'Expired'])->default('Active');
            $table->timestamp('start_date')->useCurrent(); // Default waktu sekarang
            $table->timestamp('end_date')->nullable(); // Nullable jika paket tidak berumur pendek saat demo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};