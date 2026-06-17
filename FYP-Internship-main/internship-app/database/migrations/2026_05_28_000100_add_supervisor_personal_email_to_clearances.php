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
        Schema::table('placement_clearances', function (Blueprint $table) {
            $table->string('supervisor_personal_email')->nullable()->after('supervisor_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placement_clearances', function (Blueprint $table) {
            $table->dropColumn('supervisor_personal_email');
        });
    }
};
