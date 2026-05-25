<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code')->unique(); 
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('service_id')->constrained('services');
            $table->integer('amount'); 
            $table->string('proof_of_payment')->nullable(); 
            $table->enum('status', ['Pending', 'Success', 'Failed'])->default('Pending'); 
            $table->timestamps(); // updated_at dikunci setelah status "Success" via Eloquent Event/Controller
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};