<?php
namespace nochso\WriteMe\Placeholder;

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

    public function call(\nochso\WriteMe\Placeholder\Call $call)
    {
        $this->options = $this->getDefaultOptionList();
        $this->options->prepare($call->getDocument()->getFrontmatter());        
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
