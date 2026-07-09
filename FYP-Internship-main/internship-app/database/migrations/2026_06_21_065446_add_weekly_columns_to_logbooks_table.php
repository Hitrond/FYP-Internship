<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Retained for databases that have already recorded this migration.
        // The weekly columns are now created by the original logbooks migration.
    }

    public function down(): void
    {
        //
    }
};
