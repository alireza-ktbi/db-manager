<?php

namespace Alireza\DbManager;

use Alireza\DbManager\Errors\{DatabaseInstanceDoesNotExistException, DatabaseTypeNotFoundException};
use Exception;
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
                default => throw new DatabaseTypeNotFoundException()
            };
    }

    private function createInstancePdo(DBInfo $DBInfo): PDO
    {
        $dsn = "mysql:host=$DBInfo->DbHost;dbname=$DBInfo->DbName";
        if (isset($DBInfo->DbPort)) $dsn .= ";port=" . $DBInfo->DbPort;
        return new PDO($dsn, $DBInfo->DbUser, $DBInfo->DbPass);
    }

    /**
     * @throws DatabaseInstanceDoesNotExistException
     */
    public function getInstance(DBInfo $DBInfo): PDO
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