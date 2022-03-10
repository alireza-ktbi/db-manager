<?php

namespace Alireza\DbManager;

use Alireza\DbManager\Errors\{DatabaseInstanceDoesNotExistException, DatabaseTypeNotFoundException};
use Exception;
use mysqli;
use PDO;
use PDOStatement;

class DBMNGR
{
    private static array $databases;

    /**
     * @throws DatabaseTypeNotFoundException
     */
    public function createInstance(DBInfo $DBInfo)
    {
        self::$databases[$DBInfo->DbType][$DBInfo->DbName] =
            match ($DBInfo->DbType) {
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
        if (isset(self::$databases[$DBInfo->DbType][$DBInfo->DbName])) {
            return self::$databases[$DBInfo->DbType][$DBInfo->DbName];
        } else {
            throw new DatabaseInstanceDoesNotExistException();
        }
    }

    /**
     * @throws DatabaseInstanceDoesNotExistException
     */
    public function getInstancePdo(DBInfo $DBInfo): PDO
    {
        if (isset(self::$databases[DBType::PDO][$DBInfo->DbName])) {
            return self::$databases[DBType::PDO][$DBInfo->DbName];
        } else {
            throw new DatabaseInstanceDoesNotExistException();
        }
    }

    /**
     * @throws DatabaseInstanceDoesNotExistException
     */
    public function getInstanceMysqli(DBInfo $DBInfo): Mysqli
    {
        if (isset(self::$databases[DBType::MYSQL][$DBInfo->DbName])) {
            return self::$databases[DBType::MYSQL][$DBInfo->DbName];
        } else {
            throw new DatabaseInstanceDoesNotExistException();
        }
    }

    /**
     * @throws DatabaseInstanceDoesNotExistException
     */
    public function getInstanceMongoDb(DBInfo $DBInfo): \MongoDB\Database
    {
        if (isset(self::$databases[DBType::MONGODB][$DBInfo->DbName])) {
            return self::$databases[DBType::MONGODB][$DBInfo->DbName];
        } else {
            throw new DatabaseInstanceDoesNotExistException();
        }
    }

    /**
     * @throws Exception
     */
    public function ErrorHandlePDO(PDOStatement|false $stmt)
    {
        if (!$stmt) {
            throw new Exception(
                "database error in CLASS: " . __CLASS__ . " METHOD: " . __METHOD__ . " ErrorCode: " .
                $stmt->errorCode() . " ErrorInfo: " . json_encode($stmt->errorInfo(), true)
            );
        }
    }
}