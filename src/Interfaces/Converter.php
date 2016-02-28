<?php
namespace nochso\WriteMe\Interfaces;

use nochso\WriteMe\Document;

interface Converter
{
    /**
     * @param \nochso\WriteMe\Document                 $document
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders
     */
    public function convert(Document $document, array $placeholders);
}
