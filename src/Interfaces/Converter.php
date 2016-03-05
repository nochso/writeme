<?php
namespace nochso\WriteMe\Interfaces;

use nochso\WriteMe\Document;

interface Converter
{
    /**
     * @param \nochso\WriteMe\Document                 $document
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $registeredPlaceholders Key must be the placeholder's identifier.
     */
    public function convert(Document $document, array $registeredPlaceholders);
}
