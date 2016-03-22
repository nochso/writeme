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
     * @return \nochso\WriteMe\Placeholder\OptionList[]
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('toc.max-depth', 'Maximum depth of header level to extract.', 3),
        ]);
    }

    /**
     * convertMarkdownLinksToText by stripping links and leaving only the link text.
     *
     * @param string $markdown
     *
     * @return string Markdown with links replaced by only the link text.
     */
    private function convertMarkdownLinksToText($markdown)
    {
        $regex = "
/(?<!\\\\)\\[          # start of link text must not be escaped
(.+?)
(?<!\\\\)\\]           # end of link text must not be escaped
(
\\(.+?(?<!\\\\)\\)     # match either '(foo)'
|
\\ ?\\[.*?(?<!\\\\)\\] # or ' [foo]' or '[foo]' or '[]'
)?                     # both are optional
/x";
        return preg_replace($regex, '$1', $markdown);
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
            $cleanHeader = new Markdown\Header($header->getLevel(), $this->convertMarkdownLinksToText($header->getText()));
            $toc .= $indent . '- [' . $cleanHeader->getText() . '](#' . $cleanHeader->getAnchor() . ")\n";
        }
        return rtrim($toc, "\n");
    }
}
