<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_clearances', function (Blueprint $table) {
            $table->foreignId('placement_clearance_id')
                ->nullable()
                ->after('student_id')
                ->constrained('placement_clearances')
                ->nullOnDelete();
            $table->string('slides_path')->nullable()->change();
            $table->string('slides_original_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('final_clearances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('placement_clearance_id');
            $table->string('slides_path')->nullable(false)->change();
            $table->string('slides_original_name')->nullable(false)->change();
        });
    }
};
