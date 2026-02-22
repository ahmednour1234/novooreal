<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup all database tables';

  public function handle()
{
    $databaseName = DB::getDatabaseName();
    $backupFileName = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
    $backupPath = public_path('backups/' . $backupFileName);

    if (!file_exists(public_path('backups'))) {
        mkdir(public_path('backups'), 0755, true);
    }

    $tables = DB::select('SHOW TABLES');

    $handle = fopen($backupPath, 'w');

    foreach ($tables as $table) {
        $tableName = $table->{'Tables_in_' . $databaseName};
        $tableData = DB::table($tableName)->get();

        fwrite($handle, "DROP TABLE IF EXISTS $tableName;\n");
        $createTable = DB::selectOne("SHOW CREATE TABLE $tableName");
        fwrite($handle, $createTable->{'Create Table'} . ";\n");

        foreach ($tableData as $row) {
            $columns = implode('`, `', array_keys((array) $row));
            $values = implode("', '", array_map('addslashes', array_values((array) $row)));
            fwrite($handle, "INSERT INTO `$tableName` (`$columns`) VALUES ('$values');\n");
        }
    }

    fclose($handle);

    $this->info('Database backup completed successfully! Backup file: ' . $backupFileName);
}
}