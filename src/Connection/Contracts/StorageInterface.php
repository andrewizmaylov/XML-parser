<?php

namespace XMLToDB\XmlParser\Connection\Contracts;

interface StorageInterface
{
    public const string TABLE_NAME = 'xml_data';

    public function checkTableExists(string $tableName): void;

    public function upsertMany(array $data, string $tableName, string $source): void;
}
