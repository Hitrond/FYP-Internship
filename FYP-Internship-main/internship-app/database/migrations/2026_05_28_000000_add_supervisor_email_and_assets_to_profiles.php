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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('company_email')->nullable()->after('supervisor_contact_number');
            $table->string('signature_path')->nullable()->after('industry');
            $table->string('stamp_path')->nullable()->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['company_email', 'signature_path', 'stamp_path']);
        });
    }
};
