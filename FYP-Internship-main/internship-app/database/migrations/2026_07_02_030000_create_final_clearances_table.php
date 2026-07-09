<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_path');
            $table->string('report_original_name');
            $table->string('slides_path');
            $table->string('slides_original_name');
            $table->string('status', 20)->default('pending');
            $table->string('mentor_status', 20)->default('pending');
            $table->text('mentor_feedback')->nullable();
            $table->timestamp('mentor_signed_at')->nullable();
            $table->boolean('industrial_hours_completed')->default(false);
            $table->boolean('company_property_cleared')->default(false);
            $table->string('supervisor_status', 20)->default('pending');
            $table->text('supervisor_feedback')->nullable();
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['mentor_id', 'mentor_status']);
            $table->index(['supervisor_id', 'supervisor_status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_clearances');
    }
};
