<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_question_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('questions');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['resume_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_question_sets');
    }
};
