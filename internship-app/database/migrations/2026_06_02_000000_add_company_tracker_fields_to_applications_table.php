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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();

            $table->string('company_name')->after('user_id');
            $table->string('position_title')->nullable()->after('company_name');
            $table->string('location')->nullable()->after('position_title');

            $table->string('status')->default('Interested')->after('location');

            $table->date('applied_on')->nullable()->after('status');
            $table->date('last_contacted_on')->nullable()->after('applied_on');
            $table->date('next_followup_on')->nullable()->after('last_contacted_on');

            $table->string('job_url')->nullable()->after('next_followup_on');
            $table->text('notes')->nullable()->after('job_url');

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);

            $table->dropColumn([
                'notes',
                'job_url',
                'next_followup_on',
                'last_contacted_on',
                'applied_on',
                'status',
                'location',
                'position_title',
                'company_name',
            ]);

            $table->dropConstrainedForeignId('user_id');
        });
    }
};
