<?php

namespace XMLToDB\XmlParser\Connection\Contracts;


use XMLToDB\XmlParser\Entities\ParsedEntity;

interface RepositoryInterface
{
    public function getLatestRecord(string $tableName, string $source): ?ParsedEntity;
}
