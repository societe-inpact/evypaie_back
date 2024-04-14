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
        Schema::create('mapping_entity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_entity_id')->constrained('company_entities')->cascadeOnDelete();
            $table->foreignId('mapping_id')->constrained('mapping')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping_entity');
    }
};
