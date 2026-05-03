<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_path'); // encrypted path on disk
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size_bytes');
            $table->longText('extracted_text')->nullable();
            $table->boolean('is_encrypted')->default(true);
            $table->boolean('shared_with_recruiters')->default(false);
            $table->timestamp('purge_after')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
