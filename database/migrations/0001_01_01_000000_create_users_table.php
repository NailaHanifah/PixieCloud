<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // INT Primary Key, Auto Increment
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ministack_access_key')->nullable(); // Nullable untuk awal
            $table->string('ministack_secret_key')->nullable(); // Nullable untuk awal
            $table->timestamps(); // Mengcover created_at dan updated_at secara otomatis
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};