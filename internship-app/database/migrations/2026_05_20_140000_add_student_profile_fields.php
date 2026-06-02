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
            $table->string('tp_number', 30)->nullable()->after('user_id');
            $table->string('full_name')->nullable()->after('tp_number');
            $table->string('course_name')->nullable()->after('full_name');
            $table->string('specialization')->nullable()->after('course_name');
            $table->string('intake_code', 30)->nullable()->after('specialization');
            $table->string('personal_email')->nullable()->after('intake_code');
            $table->string('contact_number', 30)->nullable()->after('personal_email');
            $table->string('internship_status', 30)->nullable()->after('contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tp_number',
                'full_name',
                'course_name',
                'specialization',
                'intake_code',
                'personal_email',
                'contact_number',
                'internship_status',
            ]);
        });
    }
};
