<?php

namespace XMLToDB\XmlParser\Connection\Contracts;

interface StorageInterface
{
    public function tableExists(string $tableName): bool;

    public function upsertMany(array $data, string $tableName, string $source): void;
}
