<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('T_Notify_Read')) {
            return;
        }

        Schema::create('T_Notify_Read', function (Blueprint $table) {
            $table->bigIncrements('id_notify_read_key');
            $table->unsignedBigInteger('id_notify_key')->index();
            $table->string('member_id', 64)->index();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('dtt_added')->nullable();
            $table->timestamp('dtt_mod')->nullable();

            $table->unique(['id_notify_key', 'member_id'], 't_notify_read_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('T_Notify_Read');
    }
};
