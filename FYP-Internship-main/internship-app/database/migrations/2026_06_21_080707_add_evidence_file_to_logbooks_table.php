<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->string('evidence_file_path')->nullable(); // nullable means it is optional!
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn('evidence_file_path');
        });
    }
};
