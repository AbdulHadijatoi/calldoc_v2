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
        Schema::create('doctors_information', function (Blueprint $table) {
            $table->id();
            $table->string('query')->nullable();
            $table->string('google_place_url')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_types')->nullable();
            $table->string('category')->nullable();
            $table->text('full_address')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors_information');
    }
};
