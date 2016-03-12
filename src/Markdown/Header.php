<?php
namespace nochso\WriteMe\Markdown;

/**
 * Header represents a single Markdown header.
 *
 * This is to help create Github-style anchor links to these headers.
 */
class Header
{
    /**
     * @var int
     */
    private $level;
    /**
     * @var string
     */
    private $text;
    /**
     * Unique counter to allow multiple headers with the same text.
     *
     * @var int
     */
    private $uniqueCounter = 0;

    /**
     * @param int    $level
     * @param string $text
     */
    public function __construct($level, $text)
    {
        $this->level = $level;
        $this->text = $text;
    }

    /**
     * @param int $value
     */
    public function setUniqueCounter($value)
    {
        $this->uniqueCounter = $value;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @see https://github.com/jch/html-pipeline/blob/master/lib/html/pipeline/toc_filter.rb
     *
     * @return string
     */
    public function getAnchor()
    {
        $anchor = strtolower($this->text);
        $anchor = preg_replace('/([^\w -]+)/', '', $anchor);
        $anchor = preg_replace('/ /', '-', $anchor);
        $uniqueSuffix = '';
        if ($this->uniqueCounter > 0) {
            $uniqueSuffix = '-' . $this->uniqueCounter;
        }
        return $anchor . $uniqueSuffix;
    }

    /**
     * @return string
     */
    public function toMarkdown()
    {
        $header = str_repeat('#', $this->level);
        $headerText = sprintf('%s %s', $header, $this->text);
        // Trim trailing space if $text is empty.
        return rtrim($headerText, ' ');
    }
}
