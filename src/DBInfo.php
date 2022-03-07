<?php

namespace Alireza\DBManagement;

class DBInfo
{
    public string $DbType;
    public string $DbName;
    public string $DbHost;
    public string $DbUser;
    public string $DbPass;
    public ?int $DbPort = null;
}