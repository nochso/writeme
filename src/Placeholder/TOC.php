<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Markdown;

/**
 * TOC placeholder creates a table of contents from Markdown headers.
 */
class TOC extends AbstractPlaceholder
{
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
        parent::apply($document);
        if (!Converter::contains($this, $document)) {
            return;
        }
        $this->document = $document;
        $parser = new Markdown\HeaderParser();
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
        $maxDepth = $this->options->getValue('toc.max-depth');
        $headers = $headerList->getHeadersWithinMaxDepth($maxDepth);
        foreach ($headers as $header) {
            $indent = str_repeat('    ', $header->getLevel() - 1);
            $toc .= $indent . '- [' . $header->getText() . '](#' . $header->getAnchor() . ")\n";
        }
        return $toc;
    }

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList[]
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('toc.max-depth', 'Maximum depth of header level to extract.', 3),
        ]);
    }
}
