# XML Processor

The package allows you to import data from an external XML file into a specified database table.
Data mapping is defined using a mask (pattern).

Large files are read and written to the database in chunks, ensuring application stability and controlled memory usage.

The package supports resuming data import after connection loss or manual stop.
Data is considered unique if it is received from a single source within a single day.

## Requirements

- PHP 8.2

## Installation

```sh

composer require andrewizmaylov/XML-parser

```

## Usage

Run the migration to create the table into which data will be imported:

```php
(new ContentTableMigration($pdo, 'table_name'))->up();
```

Usage in a controller:

```php
<?php

declare(strict_types=1);

use XMLToDB\XmlParser\Connection\PareserRepository;
use XMLToDB\XmlParser\Connection\ParserStorage;
use XMLToDB\XmlParser\Parser\XmlParser;
use XMLToDB\XmlParser\Database\Migration\ContentTableMigration;
use XMLToDB\XmlParser\Service\ReedContentToDB;

class ImportToDB
{
    public static function reedData(): ParseResult 
    {
        $pdo = new PDO('mysql:host=localhost;dbname=my_database', 'root', '');
        $tableName = 'xml_data';

        $storage = new ParserStorage($pdo);
        $repository = new PareserRepository($pdo);
        $parser = new XmlParser();
        $pattern = '/(<trip[^>]*>[\s\S]*?<\/trip>)/si';
    
        return (new ReedContentToDB(
            $storage,
            $repository,
            $parser,
        ))->reed($filePath, $pattern, $tableName);
    }
}
```
