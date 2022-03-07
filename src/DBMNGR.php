<?php

namespace Alireza\DbManager;

use Alireza\DbManager\Errors\{DatabaseInstanceDoesNotExistException, DatabaseTypeNotFoundException};
use mysqli;
use PDO;

class DBMNGR
{
    private static array $databases;

    /**
     * @throws DatabaseTypeNotFoundException
     */
    public function createInstance(DBInfo $databaseInfo)
    {
        self::$databases[$databaseInfo->DbName . $databaseInfo->DbType] = match ($databaseInfo->DbType) {
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
            "mongodb://" . $databaseInfo->DbPass . $databaseInfo->DbHost . ":" . $databaseInfo->DbPort ?? "27017"
        );
        return $client->{$databaseInfo->DbName};
    }

    /**
     * @throws DatabaseInstanceDoesNotExistException
     */
    public function getInstance(DBInfo $databaseInfo): mysqli|PDO|\MongoDB\Database
    {
        if (isset(self::$databases[$databaseInfo->DbName . $databaseInfo->DbType])) {
            return self::$databases[$databaseInfo->DbName . $databaseInfo->DbType];
        } else {
            throw new DatabaseInstanceDoesNotExistException();
        }
    }
}