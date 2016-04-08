<?php
namespace nochso\WriteMe\Placeholder;

use nochso\Omni\Multiline;
use nochso\WriteMe\Markdown;

/**
 * TOC placeholder creates a table of contents from Markdown headers.
 *
 * There are two types of tables:
 *
 * `@toc@` collects **all** Markdown headers contained in the document with a
 * configurable maximum depth.
 *
 * `@toc.sub@` collects Markdown headers that are **below** the placeholder and on the same or deeper level.
 * If there's a header above the placeholder, its depth will be used as a minimum depth.
 * If there's no header above the placeholder, the first header after the placeholder will be used for the minimum depth.
 * There is currently no maximum depth for `@toc.sub@`.
 *
 * e.g.
 * ```markdown
 * # ignore me
 *
 * @toc.sub@
 * ## sub 1
 * # ignore me again
 * ```
 * is converted into
 *
 * ```markdown
 * # ignore me
 * - [sub 1](#sub-1)
 * ## sub 1
 * # ignore me again
 * ```
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

    public function toc(Call $call)
    {
        $parser = new Markdown\HeaderParser();
        $headerList = $parser->extractHeaders($call->getDocument());
        $maxDepth = $this->options->getValue('toc.max-depth');
        $headers = $headerList->getHeadersWithinMaxDepth($maxDepth);
        $toc = $this->formatTOC($headers);
        $call->replace($toc);
    }

    public function tocSub(Call $call)
    {
        $parser = new Markdown\HeaderParser();
        $headerList = $parser->extractHeaders($call->getDocument());
        $lines = Multiline::create($call->getDocument()->getContent());
        $lineIndex = $lines->getLineIndexByCharacterPosition($call->getStartPositionOfRawCall());
        $headers = $headerList->getHeadersBelowLine($lineIndex);
        $toc = $this->formatTOC($headers);
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
        $maxDepth = $this->options->getValue('toc.max-depth');
        $headers = $headerList->getHeadersWithinMaxDepth($maxDepth);
        return $this->formatTOC($headers);
    }

    private function createSubTOC(Call $call, Markdown\HeaderList $headerList)
    {
        $lines = Multiline::create($call->getDocument()->getContent());
        $lineIndex = $lines->getLineIndexByCharacterPosition($call->getStartPositionOfRawCall());
        $headers = $headerList->getHeadersBelowLine($lineIndex);
        return $this->formatTOC($headers);
    }

    /**
     * @param Markdown\Header[] $headers
     *
     * @return mixed
     */
    private function formatTOC(array $headers)
    {
        $toc = '';
        // Normalize indentation of headers
        $minLevel = PHP_INT_MAX;
        foreach ($headers as $header) {
            $minLevel = min($minLevel, $header->getLevel());
        }
        foreach ($headers as $header) {
            $indent = str_repeat('    ', $header->getLevel() - $minLevel);
            // Recreate the header without Markdown links
            $cleanHeader = clone $header;
            $cleanHeader->setText($this->convertMarkdownLinksToText($header->getText()));
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
