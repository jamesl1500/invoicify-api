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
        Schema::table('clients', function (Blueprint $table) {
            // 
            $table->string('onboard_status')->default('pending')->after('email_verified_at');
            $table->string('onboard_token')->nullable()->after('onboard_status');
            $table->timestamp('onboard_token_expires_at')->nullable()->after('onboard_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
