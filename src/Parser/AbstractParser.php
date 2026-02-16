<?php

declare(strict_types=1);

namespace AndreyIzmaylov\XmlParser\Parser;

use AndreyIzmaylov\XmlParser\Exceptions\ParserException;
use AndreyIzmaylov\XmlParser\Parser\Contracts\ParserInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractParser implements ParserInterface
{
    /**
     * @var false|resource
     */
    protected $file;

    protected readonly string $filePath;

    protected int|false $fileSize;

    public function __construct(
        protected readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws ParserException
     */
    public function openFile(string $filePath): void
    {
        $this->filePath = $filePath;

        if (!file_exists($this->filePath)) {
            $this->logger->error('File not found: ' . $this->filePath);
            throw new ParserException('File not found');
        }

        $this->file = fopen($this->filePath, 'rb');

        $this->fileSize = filesize($this->filePath);
    }
}
