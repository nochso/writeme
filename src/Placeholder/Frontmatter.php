<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;

class Frontmatter extends AbstractPlaceholder
{
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $identifier
     * @param mixed  $value
     */
    public function __construct($identifier, $value)
    {
        $this->identifier = $identifier;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document)
    {
        Converter::replace($this, $this->value, $document);
    }
}
