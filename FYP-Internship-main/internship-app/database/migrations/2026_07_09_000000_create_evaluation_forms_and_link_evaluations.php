<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->string('version', 50)->nullable();
            $table->json('criteria');
            $table->text('instructions')->nullable();
            $table->string('uploaded_file_path')->nullable();
            $table->string('uploaded_file_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['internship_cycle_id', 'type', 'is_active']);
        });

        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->foreignId('evaluation_form_id')
                ->nullable()
                ->after('internship_cycle_id')
                ->constrained('evaluation_forms')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evaluation_form_id');
        });

        Schema::dropIfExists('evaluation_forms');
    }
};
