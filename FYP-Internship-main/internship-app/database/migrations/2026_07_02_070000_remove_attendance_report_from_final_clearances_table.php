<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_clearances', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_report_path',
                'attendance_report_original_name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('final_clearances', function (Blueprint $table) {
            $table->string('attendance_report_path')->nullable();
            $table->string('attendance_report_original_name')->nullable();
        });
    }
};
