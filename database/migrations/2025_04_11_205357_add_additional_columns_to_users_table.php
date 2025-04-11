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
        Schema::table('users', function (Blueprint $table) {
            //
            // Add new columns to the users table
            $table->string('phone_number')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone_number');
            $table->string('company_address')->nullable()->after('company_name');
            $table->string('company_phone_number')->nullable()->after('company_address');
            $table->string('company_email')->nullable()->after('company_phone_number');
            $table->string('company_logo')->nullable()->after('company_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            // Drop the new columns from the users table
            $table->dropColumn('phone_number');
            $table->dropColumn('company_name');
            $table->dropColumn('company_address');
            $table->dropColumn('company_phone_number');
            $table->dropColumn('company_email');
            $table->dropColumn('company_logo');
        });
    }
};
