<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid('invoice_id');
            $table->foreignId('client_id');
            $table->foreignId('user_id');
            $table->string('payment_method');
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id')->unique();
            $table->enum('status', ['pending', 'completed', 'refunded', 'failed'])->default('pending');
            $table->timestamp('payment_date')->useCurrent();
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
