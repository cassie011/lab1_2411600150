<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            // Teacher who took the attendance
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date');
            $table->string('status', 10); // present, late, absent, excused
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            // One record per student per day
            $table->unique(['student_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
