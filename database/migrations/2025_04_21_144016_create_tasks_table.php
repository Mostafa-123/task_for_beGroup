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
            $table->string('name');
            $table->text ('description');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->string('image')->nullable();
            $table->string('deadline')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('assign_to')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
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
