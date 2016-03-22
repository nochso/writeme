<?php
namespace nochso\WriteMe\Markdown;

/**
 * HeaderContent represents a Markdown header and the content it contains.
 */
class HeaderContent extends Header
{
    /**
     * @var string
     */
    private $content;

    /**
     * @param \nochso\WriteMe\Markdown\Header $header
     *
     * @return \nochso\WriteMe\Markdown\HeaderContent
     */
    public static function fromHeader(Header $header)
    {
        return new self($header->getLevel(), $header->getText());
    }

    /**
     * @param string $line
     */
    public function addContent($line)
    {
        $this->content .= $line . "\n";
    }

    /**
     * @return string
     */
    public function toMarkdown()
    {
        return sprintf("%s\n%s", parent::toMarkdown(), $this->content);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
