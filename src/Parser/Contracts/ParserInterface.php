<?php

namespace AndreyIzmaylov\XmlParser\Parser\Contracts;

use Generator;

interface ParserInterface
{
    public function openFile(string $filePath): void;
    public function readData(array $params): Generator;
}
