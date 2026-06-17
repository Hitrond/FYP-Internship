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
            $table->string('mentor_staff_id', 50)->nullable()->after('portfolio_url');
            $table->string('mentor_department')->nullable()->after('mentor_staff_id');
            $table->boolean('notify_email_missed_logbook')->default(true)->after('mentor_department');
            $table->boolean('notify_dashboard_only')->default(false)->after('notify_email_missed_logbook');
            $table->string('supervisor_job_title', 100)->nullable()->after('notify_dashboard_only');
            $table->string('supervisor_contact_number', 30)->nullable()->after('supervisor_job_title');
            $table->string('company_name')->nullable()->after('supervisor_contact_number');
            $table->string('company_address')->nullable()->after('company_name');
            $table->string('industry', 100)->nullable()->after('company_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'mentor_staff_id',
                'mentor_department',
                'notify_email_missed_logbook',
                'notify_dashboard_only',
                'supervisor_job_title',
                'supervisor_contact_number',
                'company_name',
                'company_address',
                'industry',
            ]);
        });
    }
};
