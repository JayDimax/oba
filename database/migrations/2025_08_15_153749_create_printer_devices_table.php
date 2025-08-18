<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('printer_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('device_ip');
            $table->string('printer_mac');
            $table->string('device_name')->nullable();
            $table->timestamp('last_seen');
            $table->boolean('is_online')->default(true);
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->unique('agent_id'); // One printer per agent
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_devices');
    }
};
