<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: an account with transaction history cannot be deleted (keeps the audit trail)
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer']); // DB supports transfer
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
