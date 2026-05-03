<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('company')->nullable();
            $table->string('location')->nullable();
            $table->text('description');
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['recruiter_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
