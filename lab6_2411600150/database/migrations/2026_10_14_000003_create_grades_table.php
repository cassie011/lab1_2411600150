<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // Deleting a student also deletes their grades
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            // Teacher who recorded the grade (kept even if the account is removed)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('subject', 100);
            $table->unsignedTinyInteger('units')->default(3);
            $table->string('term', 20); // prelim, midterm, finals
            $table->decimal('grade', 3, 2); // 0.00 - 4.00 (same scale as GPA)
            $table->text('remarks')->nullable();
            $table->timestamps();

            // One grade per student, subject and term
            $table->unique(['student_id', 'subject', 'term']);
            $table->index(['term', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
