<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlsrv';

    public function shouldRun(): bool
    {
        return ! app()->runningUnitTests();
    }

    public function up(): void
    {
        $schema = Schema::connection($this->getConnection());

        if (! $schema->hasTable('Users_App') || $schema->hasColumn('Users_App', 'is_admin')) {
            return;
        }

        $schema->table('Users_App', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });

        DB::connection($this->getConnection())
            ->table('Users_App')
            ->whereRaw('LOWER(LTRIM(RTRIM(PrvcusID))) = ?', ['admin'])
            ->update(['is_admin' => 1]);
    }

    public function down(): void
    {
        $schema = Schema::connection($this->getConnection());

        if (! $schema->hasTable('Users_App') || ! $schema->hasColumn('Users_App', 'is_admin')) {
            return;
        }

        $schema->table('Users_App', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
