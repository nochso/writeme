<?php
namespace nochso\WriteMe\Markdown;

/**
 * HeaderList keeps track of multiple Markdown headers.
 */
class HeaderList
{
    /**
     * @var \nochso\WriteMe\Markdown\Header[]
     */
    private $headers = [];
    /**
     * @var int[]
     */
    private $uniqueTextCount = [];

    /**
     * Add a header while keeping track of duplicate texts for unique anchor links.
     *
     * @param \nochso\WriteMe\Markdown\Header $header
     */
    public function add(Header $header)
    {
        if (!isset($this->uniqueTextCount[$header->getText()])) {
            $this->uniqueTextCount[$header->getText()] = -1;
        }
        $this->uniqueTextCount[$header->getText()]++;
        $header->setUniqueCounter($this->uniqueTextCount[$header->getText()]);
        $this->headers[] = $header;
    }

    /**
     * @return \nochso\WriteMe\Markdown\Header[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param int $maxDepth
     *
     * @return \nochso\WriteMe\Markdown\Header[]
     */
    public function getHeadersWithinMaxDepth($maxDepth)
    {
        $limiter = function (Header $header) use ($maxDepth) {
            return $header->getLevel() <= $maxDepth;
        };
        return array_filter($this->headers, $limiter);
    }
}
