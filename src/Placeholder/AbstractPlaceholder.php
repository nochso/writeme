<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;

/**
 * AbstractPlaceholder.
 */
abstract class AbstractPlaceholder implements Placeholder
{
    /**
     * @var OptionList
     */
    protected $options;

    /**
     * @return string
     */
    abstract public function getIdentifier();

    /**
     * Apply must be called by inheriting classes, otherwise options won't be prepared.
     *
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document)
    {
        $this->options = $this->getDefaultOptionList();
        $this->options->prepare($document->getFrontmatter());
    }

    /**
     * getOptions returns a list of options that are used by this placeholder.
     *
     * You should not use this directly. Instead access the options property after calling `parent::apply`.
     *
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList()
    {
        return new OptionList([]);
    }
}
