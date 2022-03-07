<?php

namespace Alireza\DBManagement;

use Alireza\DBManagement\Errors\DatabaseTypeNotFoundException;

class DBMG
{
    private array $databases;

    /**
     * @throws DatabaseTypeNotFoundException
     */
    public function createInstance(DBInfo $databaseInfo)
    {
        $this->databases[$databaseInfo->DbName . $databaseInfo->DbType] = match ($databaseInfo->DbType) {
            DBType::PDO => $this->getInstancePdo($databaseInfo),
            default => throw new DatabaseTypeNotFoundException()
        };
    }

    private function getInstancePdo(DBInfo $databaseInfo): \PDO
    {
        $dsn = "mysql:host=$databaseInfo->DbHost;dbname=$databaseInfo->DbName";
        if (isset($databaseInfo->DbPort)) $dsn .= ";port=" . strval($databaseInfo->DbPort);
        return new \PDO($dsn, $databaseInfo->DbUser, $databaseInfo->DbPass,);
    }
}