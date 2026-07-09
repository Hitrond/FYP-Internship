<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->boolean('timeline_generated')->default(false)->after('week_number');
            $table->timestamp('submission_due_at')->nullable()->after('end_date');
            $table->timestamp('locked_at')->nullable()->after('submission_due_at');
            $table->string('extension_status', 20)->nullable()->after('locked_at');
            $table->text('extension_reason')->nullable()->after('extension_status');
            $table->timestamp('extension_requested_at')->nullable()->after('extension_reason');
            $table->timestamp('extension_until')->nullable()->after('extension_requested_at');
            $table->text('extension_decision_note')->nullable()->after('extension_until');
            $table->timestamp('extension_decided_at')->nullable()->after('extension_decision_note');
            $table->index(['status', 'submission_due_at']);
            $table->index('extension_status');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropIndex(['status', 'submission_due_at']);
            $table->dropIndex(['extension_status']);
            $table->dropColumn([
                'timeline_generated',
                'submission_due_at',
                'locked_at',
                'extension_status',
                'extension_reason',
                'extension_requested_at',
                'extension_until',
                'extension_decision_note',
                'extension_decided_at',
            ]);
            $table->text('description')->nullable(false)->change();
        });
    }
};
