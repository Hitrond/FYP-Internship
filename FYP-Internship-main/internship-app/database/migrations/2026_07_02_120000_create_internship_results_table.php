<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('final_evaluation_id')
                ->nullable()
                ->constrained('performance_evaluations')
                ->nullOnDelete();
            $table->unsignedTinyInteger('approved_logbooks');
            $table->unsignedTinyInteger('total_logbooks')->default(16);
            $table->unsignedTinyInteger('supervisor_score');
            $table->string('result', 10);
            $table->text('rationale');
            $table->timestamp('locked_at');
            $table->timestamps();

            $table->index(['mentor_id', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_results');
    }
};
