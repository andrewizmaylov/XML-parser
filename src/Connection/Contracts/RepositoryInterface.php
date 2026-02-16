<?php

namespace AndreyIzmaylov\XmlParser\Connection\Contracts;


use AndreyIzmaylov\XmlParser\Entities\ParsedEntity;

interface RepositoryInterface
{
    public function getLatestRecord(string $tableName): ?ParsedEntity;
}
