<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Parser;


use XMLToDB\XmlParser\Exceptions\ParserException;
use Generator;

class XmlParser extends AbstractParser
{
    const int CHUNK_SIZE = 1024 * 16;

    const int BUNCH_SIZE = 50;

    protected string $pattern;

    protected int $lastReadPosition;

    private array $bunch = [];

    /**
     * @throws ParserException
     */
    public function readData(array $params): Generator
    {
        $this->openFile($params['filePath']);

        if ($this->fileSize === 0) {
            fclose($this->file);
            $this->logger->warning('The file is empty');
            return;
        }

        // Preserve params
        $this->pattern = $params['pattern'];
        $this->lastReadPosition = $params['lastReadPosition'];

        // Rewind file to last reed point
        if ($this->lastReadPosition > 0) {
            fseek($this->file, $this->lastReadPosition);
        }

        $buffer = '';
        $position = $this->lastReadPosition;
        $iterations = 0;
        $maxIterations = (int) ceil($this->fileSize / self::CHUNK_SIZE) + 1000;

        // Read forward
        while ($position < $this->fileSize && $iterations < $maxIterations) {
            $iterations++;
            $positionAtIterationStart = $position;
            fseek($this->file, $position);
            $buffer .= fread($this->file, self::CHUNK_SIZE);
            $bytesRead = strlen($buffer);

            if ($bytesRead === 0) {
                break;
            }

            $this->appendDetectedMatches($buffer, $position);

            if ($this->bunch !== []) {
                $position = $this->bunch[count($this->bunch) - 1]['endPosition'];
            } else {
                $position = $this->lastReadPosition;
            }
            if ($position <= $positionAtIterationStart) {
                $position = min($positionAtIterationStart + $bytesRead, $this->fileSize);
            }
            $buffer = '';

            if (count($this->bunch) >= self::BUNCH_SIZE) {
                $detectedRecords = $this->bunch;
                $memoryUsage = memory_get_usage() / 1024 / 1024 . ' MB' . PHP_EOL;
                $this->logger->info('[XmlParser]: ' . count($detectedRecords) . ' records processed. Memory usage: ' . $memoryUsage);

                $this->bunch = [];

                yield $detectedRecords;
            }
        }

        if ($iterations >= $maxIterations) {
            $this->logger->warning('[XmlParser]: Stopped after max iterations. Position: ' . $position . ', fileSize: ' . $this->fileSize);
        }

        if ($this->bunch !== []) {
            yield $this->bunch;
        }

        fclose($this->file);
    }

    private function appendDetectedMatches(string $buffer, int $bufferStart): void
    {
        $lastFoundedPosition = $bufferStart + strlen($buffer);

        if (preg_match_all($this->pattern, $buffer, $allMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($allMatches[0] as $match) {
                if (isset($match[1])) {
                    $position = $bufferStart + (int) $match[1];
                    $lastFoundedPosition = $position + strlen($match[0]);

                    $this->bunch[] = [
                        'content' => $match[0],
                        'startPosition' => $position,
                        'endPosition' => $lastFoundedPosition,
                    ];
                }
            }
        }

        $this->lastReadPosition = $lastFoundedPosition;
    }
}
