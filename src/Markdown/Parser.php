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
                $header = $this->extractHeader($line, $prevLine);
                if ($header !== null) {
                    $headerList->add($header);
                }
            }
            $prevLine = $line;
        }
        return $headerList;
    }

    /**
     * extractHeaderContents returns a HeaderList containing HeaderContent objects.
     *
     * @param \nochso\WriteMe\Document $document
     *
     * @return \nochso\WriteMe\Markdown\HeaderList
     *
     * @todo Refactor this as it mostly duplicates extractHeaders
     */
    public function extractHeaderContents(Document $document)
    {
        $headerList = new HeaderList();
        $lines = Multiline::create($document->getContent());
        $prevLine = null;
        $isFenced = false;
        $currentHeaderContent = null;
        $isNewHeader = false;
        foreach ($lines as $line) {
            if (preg_match('/^```(?!`)/', $line)) {
                $isFenced = !$isFenced;
            }
            if (!$isFenced) {
                $header = $this->extractHeader($line, $prevLine);
                if ($header !== null) {
                    $currentHeaderContent = HeaderContent::fromHeader($header);
                    $headerList->add($currentHeaderContent);
                    $isNewHeader = true;
                }
            }
            // Add content only if it's *after* the header line
            if ($currentHeaderContent !== null && !$isNewHeader) {
                $currentHeaderContent->addContent($line);
            }
            $prevLine = $line;
            $isNewHeader = false;
        }
        return $headerList;
    }

    /**
     * @param string      $line
     * @param string|null $prevLine
     *
     * @return \nochso\WriteMe\Markdown\Header|null
     */
    private function extractHeader($line, $prevLine)
    {
        // # ATX style header
        if (preg_match('/^(#+)\s*(.+)\s*#*$/', $line, $matches)) {
            return new Header(strlen($matches[1]), $matches[2]);
        }
        // SETEXT style header
        // ---------|=========
        if ($prevLine !== null && strlen($prevLine) !== 0 && preg_match('/^[=-]+$/', $line, $matches)) {
            $level = Strings::startsWith($line, '=') ? 1 : 2;
            return new Header($level, trim($prevLine));
        }
        return null;
    }
}
