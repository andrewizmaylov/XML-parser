<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Connection;


use XMLToDB\XmlParser\Connection\Contracts\RepositoryInterface;
use XMLToDB\XmlParser\Entities\ParsedEntity;
use PDO;
use PDOException;

class PareserRepository implements RepositoryInterface
{
    public function __construct(
        protected ?PDO $connection,
    )
    {
    }

    public function getLatestRecord(string $tableName): ?ParsedEntity
    {
        try {
            $sql = <<<'STATMENT'
                SELECT * FROM %s 
                WHERE created_at >= :today 
                AND created_at < :today + INTERVAL 1 DAY
                ORDER BY endPosition DESC 
                LIMIT 1
            STATMENT;

            $stmt = $this->connection->prepare(sprintf($sql, $tableName));
            $stmt->execute([':today' => date('Y-m-d')]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? new ParsedEntity(
                content: $result['content'],
                startPosition: $result['startPosition'],
                endPosition: $result['endPosition'],
                status: $result['status'],
                id: $result['id'],
                created_at: $result['created_at'],
                updated_at: $result['updated_at'],
            ) : null;

        } catch (PDOException $e) {
            error_log("Get latest record error: " . $e->getMessage());
            return null;
        }
    }
}
