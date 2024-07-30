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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('check_by_user_id')->nullable();
            $table->unsignedBigInteger('confirm_by_user_id')->nullable();
            $table->unsignedBigInteger('follow_up_user_id')->nullable();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('status')->default('todo')->nullable();
            $table->string('priority')->default('medium')->nullable();
            $table->integer('page_order')->default(0)->nullable();
            $table->string('follow_up_message')->nullable();
            $table->string('proof_method')->nullable();
            $table->string('invoice_reference')->nullable();
            $table->string('estimate_time')->nullable();
            $table->date('deadline')->nullable();
            $table->string('recurring_period')->nullable();
            $table->boolean('is_mark_as_done')->default(false)->nullable();
            $table->boolean('is_checked')->default(false)->nullable();
            $table->boolean('is_confirmed')->default(false)->nullable();
            $table->boolean('is_archived')->default(false)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
