<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('T_Notify_Device')) {
            return;
        }

        Schema::create('T_Notify_Device', function (Blueprint $table) {
            $table->bigIncrements('id_notify_device_key');
            $table->string('member_id', 64)->index();
            $table->string('expo_push_token')->unique();
            $table->string('platform', 24)->nullable()->index();
            $table->string('device_id', 120)->nullable()->index();
            $table->string('device_name', 160)->nullable();
            $table->string('app_version', 40)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('dtt_added')->nullable();
            $table->timestamp('dtt_mod')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('T_Notify_Device');
    }
};
