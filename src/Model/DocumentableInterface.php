<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model;

interface DocumentableInterface
{
    public function getDocumentType(): string;
    public function convertToDocument(string $locale): DocumentResult;
}
