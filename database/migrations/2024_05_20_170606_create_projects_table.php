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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->uuid('uuid')->unique();
            $table->string('company_logo')->nullable();
            $table->string('font_color')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('bg_image')->nullable();
            $table->enum('visibility', ['public', 'private', 'restricted'])->default('public');
            $table->integer('order')->default(0);
            $table->json('guest_users')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
