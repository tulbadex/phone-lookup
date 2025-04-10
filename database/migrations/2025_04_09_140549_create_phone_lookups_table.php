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
        Schema::create('phone_lookups', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();
            $table->string('phone_number')->unique();
            $table->string('type')->nullable(); // mobile/landline
            $table->string('carrier')->nullable();
            $table->string('location')->nullable();
            $table->string('country_name')->nullable();
            $table->string('country_code')->nullable();
            $table->json('raw_data')->nullable(); // full response from API
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_lookups');
    }
};
