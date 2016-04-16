<?php
namespace nochso\WriteMe\Placeholder;

use nochso\Omni\Multiline;
use nochso\WriteMe\Markdown;

/**
 * TOC placeholder creates a table of contents from Markdown headers.
 */
class TOC extends AbstractPlaceholder
{
    public function getIdentifier()
    {
        return 'toc';
    }

    /**
     * Collects **all** Markdown headers contained in the document with a
     * configurable maximum depth.
     */
    public function toc(Call $call)
    {
        $parser = new Markdown\HeaderParser();
        $headerList = $parser->extractHeaders($call->getDocument());
        $maxDepth = $this->options->getValue('toc.max-depth');
        $headers = $headerList->getHeadersWithinMaxDepth($maxDepth);
        $toc = $this->formatTOC($headers);
        $call->replace($toc);
    }
    /**
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
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     * @param int                              $maxDepth How many levels of headers you'd like to keep.
     *                                                   Defaults to zero, meaning all sub-headers are kept.
     */
    public function tocSub(Call $call, $maxDepth = 0)
    {
        $parser = new Markdown\HeaderParser();
        $headerList = $parser->extractHeaders($call->getDocument());
        $lines = Multiline::create($call->getDocument()->getContent());
        $lineIndex = $lines->getLineIndexByCharacterPosition($call->getStartPositionOfRawCall());
        $headers = $headerList->getHeadersBelowLine($lineIndex);
        if ($maxDepth > 0) {
            $minDepth = $this->getMinimumDepth($headers);
            // Filter headers that are relatively too deep
            $headers = array_filter($headers, function (Markdown\Header $header) use ($minDepth, $maxDepth) {
                return $header->getLevel() - $minDepth < $maxDepth;
            });
        }
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
     * @param \nochso\WriteMe\Markdown\Header[] $headers
     *
     * @return string
     */
    private function formatTOC(array $headers)
    {
        $toc = '';
        $minDepth = $this->getMinimumDepth($headers);
        foreach ($headers as $header) {
            // Normalize / unindent headers
            $indent = str_repeat('    ', $header->getLevel() - $minDepth);
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

    /**
     * getMinimumDepth of all headers.
     *
     * @param \nochso\WriteMe\Markdown\Header[] $headers
     *
     * @return int The depth of the biggest header.
     */
    private function getMinimumDepth(array $headers) {
        $minDepth = PHP_INT_MAX;
        foreach ($headers as $header) {
            $minDepth = min($minDepth, $header->getLevel());
        }
        return $minDepth;
    }
}
