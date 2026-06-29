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
        Schema::create('zk_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn')->index();
            $table->string('pin')->index();
            $table->timestamp('punch_time')->index();
            $table->unsignedTinyInteger('verify_type')->nullable();
            $table->unsignedTinyInteger('punch_state')->nullable();
            $table->longText('raw')->nullable();
            $table->timestamps();

            $table->unique(['device_sn', 'pin', 'punch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_attendances');
    }
};
