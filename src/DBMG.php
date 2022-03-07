<?php

namespace Alireza\DBManagement;

use Alireza\DBManagement\Errors\DatabaseTypeNotFoundException;
use PDO;
use mysqli;

class DBMG
{
    private array $databases;

    /**
     * @throws DatabaseTypeNotFoundException
     */
    public function createInstance(DBInfo $databaseInfo)
    {
        $this->databases[$databaseInfo->DbName . $databaseInfo->DbType] = match ($databaseInfo->DbType) {
            DBType::PDO => $this->createInstancePdo($databaseInfo),
            DBType::MYSQL => $this->createInstanceMysqli($databaseInfo),
            DBType::MONGODB => $this->creatInstanceMongoDb($databaseInfo),
            default => throw new DatabaseTypeNotFoundException()
        };
    }

    private function createInstancePdo(DBInfo $databaseInfo): PDO
    {
        $dsn = "mysql:host=$databaseInfo->DbHost;dbname=$databaseInfo->DbName";
        if (isset($databaseInfo->DbPort)) $dsn .= ";port=" . $databaseInfo->DbPort;
        return new PDO($dsn, $databaseInfo->DbUser, $databaseInfo->DbPass);
    }

    private function createInstanceMysqli(DBInfo $databaseInfo): mysqli
    {
        return new mysqli(
            $databaseInfo->DbHost,
            $databaseInfo->DbUser,
            $databaseInfo->DbPass,
            $databaseInfo->DbName,
            $databaseInfo->DbPort
        );
    }

    private function creatInstanceMongoDb(DBInfo $databaseInfo): \MongoDB\Database
    {
        $client = new \MongoDB\Client(
            "mongodb://".$databaseInfo->DbPass.$databaseInfo->DbHost.":".$databaseInfo->DbPort ?? "27017"
        );
        return $client->{$databaseInfo->DbName};
    }
}