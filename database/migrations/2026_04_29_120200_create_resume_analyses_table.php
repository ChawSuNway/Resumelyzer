<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending | running | completed | failed
            $table->unsignedTinyInteger('overall_score')->nullable();
            $table->unsignedTinyInteger('ats_score')->nullable();
            $table->unsignedTinyInteger('readability_score')->nullable();
            $table->unsignedTinyInteger('skills_score')->nullable();
            $table->unsignedTinyInteger('professionalism_score')->nullable();
            $table->json('parsed_sections')->nullable(); // contact/edu/exp/skills/etc
            $table->json('keyword_match')->nullable();   // matched/missing keywords
            $table->json('feedback')->nullable();        // strengths, weaknesses, suggestions
            $table->json('flags')->nullable();           // generic-term overuse, missing dates
            $table->text('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['resume_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_analyses');
    }
};
