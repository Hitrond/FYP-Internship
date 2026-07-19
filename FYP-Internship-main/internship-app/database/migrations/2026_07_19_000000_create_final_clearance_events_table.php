<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_clearance_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('final_clearance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->string('actor_role', 40);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['final_clearance_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_clearance_events');
    }
};
