<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('intake_code')->unique();
            $table->string('academic_year', 20);
            $table->date('placement_window_start');
            $table->date('placement_window_end');
            $table->unsignedTinyInteger('duration_weeks')->default(16);
            $table->unsignedTinyInteger('deadline_weekday')->default(5);
            $table->time('deadline_time')->default('23:59:00');
            $table->string('timezone', 64)->default('Asia/Singapore');
            $table->string('status', 20)->default('draft');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'placement_window_start']);
        });

        Schema::create('internship_cycle_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('enrolled');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['internship_cycle_id', 'student_id']);
            $table->index(['mentor_id', 'status']);
        });

        Schema::table('placement_clearances', function (Blueprint $table) {
            $table->foreignId('internship_cycle_id')
                ->nullable()
                ->after('student_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->foreignId('internship_cycle_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('final_clearances', function (Blueprint $table) {
            $table->foreignId('internship_cycle_id')
                ->nullable()
                ->after('student_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->foreignId('internship_cycle_id')
                ->nullable()
                ->after('student_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('internship_results', function (Blueprint $table) {
            $table->foreignId('internship_cycle_id')
                ->nullable()
                ->after('student_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('internship_results', fn (Blueprint $table) => $table->dropConstrainedForeignId('internship_cycle_id'));
        Schema::table('performance_evaluations', fn (Blueprint $table) => $table->dropConstrainedForeignId('internship_cycle_id'));
        Schema::table('final_clearances', fn (Blueprint $table) => $table->dropConstrainedForeignId('internship_cycle_id'));
        Schema::table('logbooks', fn (Blueprint $table) => $table->dropConstrainedForeignId('internship_cycle_id'));
        Schema::table('placement_clearances', fn (Blueprint $table) => $table->dropConstrainedForeignId('internship_cycle_id'));
        Schema::dropIfExists('internship_cycle_students');
        Schema::dropIfExists('internship_cycles');
    }
};
