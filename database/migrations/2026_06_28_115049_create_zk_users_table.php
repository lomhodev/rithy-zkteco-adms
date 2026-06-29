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
        Schema::create('zk_users', function (Blueprint $table) {
            $table->id();
            $table->string('pin')->unique();
            $table->string('name')->nullable();
            $table->string('privilege')->nullable();
            $table->string('password')->nullable();
            $table->string('card')->nullable();
            $table->string('group')->nullable();
            $table->string('timezone')->nullable();
            $table->longText('raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_users');
    }
};
