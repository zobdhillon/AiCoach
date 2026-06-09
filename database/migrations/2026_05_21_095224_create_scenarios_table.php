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
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->longText('system_prompt');
            $table->string('user_role')->nullable();
            $table->string('ai_role')->nullable();
            $table->json('objectives')->nullable();
            $table->string('category')->default('General');
            $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced'])->default('Intermediate');
            $table->integer('estimated_duration')->default(15);
            $table->string('icon')->default('message');
            $table->string('color')->default('purple');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
