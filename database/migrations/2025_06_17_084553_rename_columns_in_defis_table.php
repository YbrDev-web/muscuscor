<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('defis', function (Blueprint $table) {
            $table->renameColumn('niveau de difficulté', 'niveau_difficulte');
            $table->renameColumn('Type de défi',       'type');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('defis', function (Blueprint $table) {
            //
        });
    }
};
