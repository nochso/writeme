<?php
namespace nochso\WriteMe\Placeholder\TOC;

use nochso\Omni\Multiline;
use nochso\Omni\Strings;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;

class TOC implements Placeholder
{
    /**
     * Default maximum depth of header levels. Override this using `toc.max-depth`.
     */
    const MAX_DEPTH_DEFAULT = 3;

    /**
     * @var \nochso\WriteMe\Placeholder\TOC\HeaderList
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
        $this->headers = new HeaderList();
        $lines = Multiline::create($this->document->getContent());
        $prevLine = null;
        $isFenced = false;
        foreach ($lines as $line) {
            if (preg_match('/^```(?!`)/', $line)) {
                $isFenced = !$isFenced;
            }
            if (!$isFenced) {
                $this->extractHeader($line, $prevLine);
            }
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
            $this->headers->add(new Header(strlen($matches[1]), $matches[2]));
            return;
        }
        // SETEXT style header
        // ---------|=========
        if ($prevLine !== null && strlen($prevLine) !== 0 && preg_match('/^[=-]+$/', $line, $matches)) {
            $level = Strings::startsWith($line, '=') ? 1 : 2;
            $this->headers->add(new Header($level, trim($prevLine)));
        }
    }

    /**
     * @return string
     */
    private function createTOC()
    {
        $toc = '';
        $maxDepth = $this->document->getFrontmatter()->get('toc.max-depth', self::MAX_DEPTH_DEFAULT);
        $headers = $this->headers->getHeadersWithinMaxDepth($maxDepth);
        foreach ($headers as $header) {
            $indent = str_repeat('    ', $header->getLevel() - 1);
            $toc .= $indent . '- [' . $header->getText() . '](#' . $header->getAnchor() . ")\n";
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
