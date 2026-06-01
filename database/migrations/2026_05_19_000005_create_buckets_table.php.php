<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buckets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('bucket_name')->unique();
            $table->integer('allocated_size_mb');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buckets');
    }
};