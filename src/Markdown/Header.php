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
     * @var int
     */
    private $lineIndex;

    /**
     * @param int    $level
     * @param string $text
     * @param int    $lineIndex
     */
    public function __construct($level, $text, $lineIndex = null)
    {
        $this->level = $level;
        $this->text = $text;
        $this->lineIndex = $lineIndex;
    }

    /**
     * @return int
     */
    public function getUniqueCounter()
    {
        return $this->uniqueCounter;
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
     * shiftLevel of header up or down.
     *
     * @param int $amount Positive to increase, negative to decrease the level by amount.
     */
    public function shiftLevel($amount) {
        $this->level += $amount;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
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
     * @return int
     */
    public function getLineIndex()
    {
        return $this->lineIndex;
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
