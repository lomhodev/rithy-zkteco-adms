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
        Schema::create('zk_commands', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn')->index();
            $table->text('command');
            $table->string('status')->default('pending'); // pending, sent, success, failed
            $table->longText('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_commands');
    }
};
