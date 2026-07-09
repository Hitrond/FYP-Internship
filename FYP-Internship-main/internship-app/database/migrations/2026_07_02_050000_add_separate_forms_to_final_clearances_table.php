<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_clearances', function (Blueprint $table) {
            $table->string('report_clearance_form_path')->nullable()->after('report_original_name');
            $table->string('report_clearance_form_original_name')->nullable()->after('report_clearance_form_path');
            $table->string('attendance_report_path')->nullable()->after('report_clearance_form_original_name');
            $table->string('attendance_report_original_name')->nullable()->after('attendance_report_path');
        });
    }

    public function down(): void
    {
        Schema::table('final_clearances', function (Blueprint $table) {
            $table->dropColumn([
                'report_clearance_form_path',
                'report_clearance_form_original_name',
                'attendance_report_path',
                'attendance_report_original_name',
            ]);
        });
    }
};
