<?php
namespace nochso\WriteMe\Placeholder;

use nochso\Omni\Multiline;
use nochso\Omni\Strings;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;

class TOC implements Placeholder
{
    /**
     * @var array [level, header text] pairs
     */
    private $headers;
    /**
     * @var \nochso\WriteMe\Document
     */
    private $document;

    public function getIdentifier()
    {
        return 'toc';
    }

    public function apply(Document $document)
    {
        if (!Converter::contains($this, $document)) {
            return;
        }
        $this->document = $document;
        $this->extractHeaders();
        $toc = $this->createTOC();
        Converter::replace($this, $toc, $document);
    }

    private function extractHeaders()
    {
        $this->headers = [];
        $lines = Multiline::create($this->document->getContent());
        $prevLine = null;
        foreach ($lines as $line) {
            $this->extractHeader($line, $prevLine);
            $prevLine = $line;
        }
    }

    /**
     * @param string      $line
     * @param string|null $prevLine
     */
    private function extractHeader($line, $prevLine)
    {
        // # ATX style header
        if (preg_match('/^(#+)\s*(.+)\s*#*$/', $line, $matches)) {
            $this->headers[] = [strlen($matches[1]), $matches[2]];
            return;
        }
        // SETEXT style header
        // ---------|=========
        if ($prevLine !== null && strlen($prevLine) !== 0 && preg_match('/^[=-]+$/', $line, $matches)) {
            $level = Strings::startsWith($line, '=') ? 1 : 2;
            $this->headers[] = [$level, trim($prevLine)];
        }
    }

    /**
     * @return string
     */
    private function createTOC()
    {
        $toc = '';
        $maxDepth = $this->document->getFrontmatter()->get('toc.max-depth', 3);
        $depthLimiter = function ($header) use ($maxDepth) {
            return $header[0] <= $maxDepth;
        };
        $this->headers = array_filter($this->headers, $depthLimiter);
        foreach ($this->headers as $element) {
            $indent = str_repeat('    ', $element[0] - 1);
            $toc .= $indent . '- [' . $element[1] . '](#' . $this->getAnchor($element[1]) . ")\n";
        }
        return $toc;
    }

    /**
     * getAnchor turns a header string into a Github compatible anchor.
     *
     * @param string $header
     *
     * @return string
     */
    private function getAnchor($header)
    {
        $anchor = strtolower($header);
        $anchor = preg_replace('/([^\w -]+)/', '', $anchor);
        $anchor = preg_replace('/ /', '-', $anchor);
        return $anchor;
    }
}
