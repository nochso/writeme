<?php
namespace nochso\WriteMe\Interfaces;

use nochso\WriteMe\Document;

interface Placeholder
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document);

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getOptions();
}
