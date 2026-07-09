<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->json('attendance_entries')->nullable()->after('description');
            $table->unsignedInteger('rendered_minutes')->default(0)->after('attendance_entries');
            $table->unsignedInteger('verified_minutes')->nullable()->after('rendered_minutes');
            $table->text('attendance_remarks')->nullable()->after('verified_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_entries',
                'rendered_minutes',
                'verified_minutes',
                'attendance_remarks',
            ]);
        });
    }
};
