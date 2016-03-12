<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Markdown;

class TOC implements Placeholder
{
    /**
     * Default maximum depth of header levels. Override this using `toc.max-depth`.
     */
    const MAX_DEPTH_DEFAULT = 3;

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
        $parser = new Markdown\Parser();
        $headerList = $parser->extractHeaders($document);
        $toc = $this->createTOC($headerList);
        Converter::replace($this, $toc, $document);
    }

    /**
     * @param \nochso\WriteMe\Markdown\HeaderList $headerList
     *
     * @return string
     */
    private function createTOC(Markdown\HeaderList $headerList)
    {
        $toc = '';
        $maxDepth = $this->document->getFrontmatter()->get('toc.max-depth', self::MAX_DEPTH_DEFAULT);
        $headers = $headerList->getHeadersWithinMaxDepth($maxDepth);
        foreach ($headers as $header) {
            $indent = str_repeat('    ', $header->getLevel() - 1);
            $toc .= $indent . '- [' . $header->getText() . '](#' . $header->getAnchor() . ")\n";
        }
        return $toc;
    }
}
