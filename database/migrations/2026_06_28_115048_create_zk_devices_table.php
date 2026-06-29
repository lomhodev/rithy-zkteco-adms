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
        Schema::create('zk_devices', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->unique();
            $table->string('name')->nullable();
            $table->string('ip')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('online')->default(false);
            $table->json('last_payload')->nullable();
            $table->timestamp('last_user_sync_at')->nullable();
            $table->timestamp('last_attendance_sync_at')->nullable();
            $table->timestamp('last_biodata_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_devices');
    }
};
