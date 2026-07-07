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

        if (! $schema->hasTable('Users_App') || ! $schema->hasColumn('Users_App', 'Password')) {
            return;
        }

        if ($this->currentPasswordLength() === 250) {
            return;
        }

        $schema->table('Users_App', function (Blueprint $table) {
            $table->string('Password', 250)->change();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->getConnection());

        if (! $schema->hasTable('Users_App') || ! $schema->hasColumn('Users_App', 'Password')) {
            return;
        }

        if ($this->currentPasswordLength() === 40) {
            return;
        }

        $schema->table('Users_App', function (Blueprint $table) {
            $table->string('Password', 40)->change();
        });
    }

    private function currentPasswordLength(): ?int
    {
        $row = DB::connection($this->getConnection())->selectOne(
            'select CHARACTER_MAXIMUM_LENGTH
            from INFORMATION_SCHEMA.COLUMNS
            where TABLE_SCHEMA = ? and TABLE_NAME = ? and COLUMN_NAME = ?',
            ['dbo', 'Users_App', 'Password']
        );

        $length = data_get($row, 'CHARACTER_MAXIMUM_LENGTH');

        return is_numeric($length) ? (int) $length : null;
    }
};
