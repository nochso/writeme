<?php
namespace nochso\WriteMe\Placeholder;

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

    public function call(Call $call)
    {
        parent::call($call);
        $this->document = $call->getDocument();
        $parser = new Markdown\HeaderParser();
        $headerList = $parser->extractHeaders($this->document);
        $toc = $this->createTOC($headerList);
        $call->replace($toc);
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

    /**
     * TOC must be called as late as possible!
     *
     * @return int[]
     */
    public function getCallPriorities()
    {
        return [self::PRIORITY_LAST];
    }
}
