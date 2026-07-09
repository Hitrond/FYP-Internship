<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->foreignId('approved_by_id')
                ->nullable()
                ->after('rejection_category')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_id');
            $table->string('approval_signature_path')->nullable()->after('approved_at');
            $table->string('approval_stamp_path')->nullable()->after('approval_signature_path');
            $table->string('approval_company_name')->nullable()->after('approval_stamp_path');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_id');
            $table->dropColumn([
                'approved_at',
                'approval_signature_path',
                'approval_stamp_path',
                'approval_company_name',
            ]);
        });
    }
};
