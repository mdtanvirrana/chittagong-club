<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('T_AFFILIATED_CLUBS', 'Logo_Path')) {
            Schema::table('T_AFFILIATED_CLUBS', function (Blueprint $table) {
                $table->string('Logo_Path', 400)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('T_AFFILIATED_CLUBS', 'Logo_Path')) {
            Schema::table('T_AFFILIATED_CLUBS', function (Blueprint $table) {
                $table->dropColumn('Logo_Path');
            });
        }
    }
};
