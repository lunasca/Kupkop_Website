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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('species'); // Dog, Cat, etc.
            $table->string('breed')->nullable();
            $table->string('age');
            $table->string('gender'); // Male, Female
            $table->string('vaccination_status')->nullable();
            $table->string('deworming_status')->nullable();
            $table->boolean('is_spayed_neutered')->default(false);
            $table->text('special_health_notes')->nullable();
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('status')->default('Available'); // Available, Pending, Adopted, Draft
            $table->string('location')->default('Brgy. San Antonio, Pasig City');
            $table->string('rescuer_id')->nullable();
            $table->string('posted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
