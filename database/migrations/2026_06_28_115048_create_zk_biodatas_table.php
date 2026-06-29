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
        Schema::create('zk_biodatas', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn', 50)->nullable()->index();
            $table->string('pin', 50)->nullable()->index();
            $table->tinyInteger('biometric_type')->nullable();
            $table->integer('template_no')->nullable();
            $table->integer('template_index')->nullable();
            $table->boolean('valid')->nullable();
            $table->boolean('duress')->nullable();
            $table->integer('major_version')->nullable();
            $table->integer('minor_version')->nullable();
            $table->integer('format')->nullable();
            $table->longText('template')->nullable();
            $table->longText('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['device_sn', 'pin', 'biometric_type', 'template_no', 'template_index'], 'zkteco_biodata_templates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_biodatas');
    }
};
