<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Result;

use JsonSerializable;

final readonly class ParseResult implements JsonSerializable
{
    public function __construct(
        public int $statusCode,
        public string $status,
        public int $recordsAdded,
        public float $timeElapsedSeconds,
    ) {
    }

    public function timeElapsedFormatted(): string
    {
        $seconds = (int) $this->timeElapsedSeconds;
        $millis = (int) (($this->timeElapsedSeconds - $seconds) * 1000);
        if ($seconds >= 60) {
            $mins = (int) ($seconds / 60);
            $secs = $seconds % 60;
            return sprintf('%d m %d s %d ms', $mins, $secs, $millis);
        }
        return sprintf('%d s %d ms', $seconds, $millis);
    }

    public function jsonSerialize(): array
    {
        return [
            'status_code' => $this->statusCode,
            'status' => $this->status,
            'records_added' => $this->recordsAdded,
            'time_elapsed_seconds' => round($this->timeElapsedSeconds, 3),
            'time_elapsed_formatted' => $this->timeElapsedFormatted(),
        ];
    }

    public function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
    {
        return json_encode($this->jsonSerialize(), $flags);
    }

    public static function success(int $recordsAdded, float $timeElapsedSeconds): self
    {
        return new self(200, 'success', $recordsAdded, $timeElapsedSeconds);
    }

    public static function error(int $statusCode, string $message, float $timeElapsedSeconds, int $recordsAdded = 0): self
    {
        return new self($statusCode, $message, $recordsAdded, $timeElapsedSeconds);
    }
}
