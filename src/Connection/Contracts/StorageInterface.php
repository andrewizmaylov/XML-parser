<?php

namespace XMLToDB\XmlParser\Connection\Contracts;

interface StorageInterface
{
    public function upsertMany(array $data, string $tableName): void;
}
