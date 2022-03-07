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
    public function createInstance(DBInfo $DBInfo)
    {
        self::$databases[$DBInfo->DbName . $DBInfo->DbType] = match ($DBInfo->DbType) {
            DBType::PDO => $this->createInstancePdo($DBInfo),
            DBType::MYSQL => $this->createInstanceMysqli($DBInfo),
            DBType::MONGODB => $this->creatInstanceMongoDb($DBInfo),
            default => throw new DatabaseTypeNotFoundException()
        };
    }

    private function createInstancePdo(DBInfo $DBInfo): PDO
    {
        $dsn = "mysql:host=$DBInfo->DbHost;dbname=$DBInfo->DbName";
        if (isset($DBInfo->DbPort)) $dsn .= ";port=" . $DBInfo->DbPort;
        return new PDO($dsn, $DBInfo->DbUser, $DBInfo->DbPass);
    }

    private function createInstanceMysqli(DBInfo $DBInfo): mysqli
    {
        return new mysqli(
            $DBInfo->DbHost,
            $DBInfo->DbUser,
            $DBInfo->DbPass,
            $DBInfo->DbName,
            $DBInfo->DbPort
        );
    }

    private function creatInstanceMongoDb(DBInfo $DBInfo): \MongoDB\Database
    {
        $client = new \MongoDB\Client(
            "mongodb://" . $DBInfo->DbPass . $DBInfo->DbHost . ":" . $DBInfo->DbPort ?? "27017"
        );
        return $client->{$DBInfo->DbName};
    }

    /**
     * @throws DatabaseInstanceDoesNotExistException
     */
    public function getInstance(DBInfo $DBInfo): mysqli|PDO|\MongoDB\Database
    {
        if (isset(self::$databases[$DBInfo->DbName . $DBInfo->DbType])) {
            return self::$databases[$DBInfo->DbName . $DBInfo->DbType];
        } else {
            throw new DatabaseInstanceDoesNotExistException();
        }
    }
}