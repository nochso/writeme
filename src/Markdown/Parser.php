<?php
namespace nochso\WriteMe\Markdown;

use nochso\Omni\Multiline;
use nochso\Omni\Strings;
use nochso\WriteMe\Document;

/**
 * MarkdownParser extracts elements from Markdown content.
 */
class Parser
{
    /**
     * @param \nochso\WriteMe\Document $document
     *
     * @return \nochso\WriteMe\Markdown\HeaderList
     */
    public function extractHeaders(Document $document)
    {
        $headerList = new HeaderList();
        $lines = Multiline::create($document->getContent());
        $prevLine = null;
        $isFenced = false;
        foreach ($lines as $line) {
            if (preg_match('/^```(?!`)/', $line)) {
                $isFenced = !$isFenced;
            }
            if (!$isFenced) {
                $this->extractHeader($line, $prevLine, $headerList);
            }
            $prevLine = $line;
        }
        return $headerList;
    }

    /**
     * @param string                              $line
     * @param string|null                         $prevLine
     * @param \nochso\WriteMe\Markdown\HeaderList $headerList
     *
     * @return bool
     */
    private function extractHeader($line, $prevLine, HeaderList $headerList)
    {
        // # ATX style header
        if (preg_match('/^(#+)\s*(.+)\s*#*$/', $line, $matches)) {
            $headerList->add(new Header(strlen($matches[1]), $matches[2]));
            return true;
        }
        // SETEXT style header
        // ---------|=========
        if ($prevLine !== null && strlen($prevLine) !== 0 && preg_match('/^[=-]+$/', $line, $matches)) {
            $level = Strings::startsWith($line, '=') ? 1 : 2;
            $headerList->add(new Header($level, trim($prevLine)));
            return true;
        }
        return false;
    }
}
