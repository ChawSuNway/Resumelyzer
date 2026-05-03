<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiter_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['recruiter_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_notes');
    }
};
