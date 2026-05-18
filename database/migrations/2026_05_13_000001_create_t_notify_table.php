<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('T_Notify')) {
            return;
        }

        Schema::create('T_Notify', function (Blueprint $table) {
            $table->bigIncrements('id_notify_key');
            $table->unsignedInteger('id_notify_ver')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_online')->default(true)->index();
            $table->string('target_type', 32)->default('all_members')->index();
            $table->string('target_member_id', 64)->default('*')->index();
            $table->string('source_type', 32)->index();
            $table->string('source_table', 64)->nullable();
            $table->string('source_key', 64)->index();
            $table->unsignedInteger('source_version')->nullable();
            $table->string('event', 32)->default('posted')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('action_url', 2048)->nullable();
            $table->longText('payload')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedInteger('push_attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('pushed_at')->nullable()->index();
            $table->timestamp('dtt_added')->nullable();
            $table->timestamp('dtt_mod')->nullable()->index();
            $table->integer('id_user_mod')->default(0)->index();

            $table->unique(['source_type', 'source_key', 'event', 'target_type', 'target_member_id'], 't_notify_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('T_Notify');
    }
};
