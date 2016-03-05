<?php
namespace nochso\WriteMe\Placeholder;

use nochso\Omni\Multiline;
use nochso\Omni\Strings;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;

class TOC implements Placeholder
{
    public function getIdentifier()
    {
        return 'toc';
    }

    public function apply(Document $document)
    {
        if (!Converter::contains($this, $document)) {
            return;
        }
        $content = $document->getContent();
        $elements = [];
        $lines = Multiline::create($content);
        $prevLine = null;
        foreach ($lines as $line) {
            if (preg_match('/^(#+)\s*(.+)\s*#*$/', $line, $matches)) {
                $elements[] = [strlen($matches[1]), $matches[2]];
            } elseif ($prevLine !== null && strlen($prevLine) !== 0 && preg_match('/^[=-]+$/', $line, $matches)) {
                $level = Strings::startsWith($line, '=') ? 1 : 2;
                $elements[] = [$level, trim($prevLine)];
            }
            $prevLine = $line;
        }

        $toc = '';
        $maxDepth = $document->getFrontmatter()->get('toc.max-depth', 3);
        foreach ($elements as $element) {
            if ($element[0] <= $maxDepth) {
                $indent = str_repeat('    ', $element[0] - 1);
                $toc .= $indent . '- [' . $element[1] . '](#' . $this->getAnchor($element[1]) . ")\n";
            }
        }
        Converter::replace($this, $toc, $document);
    }

    private function getAnchor($text)
    {
        $anchor = strtolower($text);
        $anchor = preg_replace('/([^\w -]+)/', '', $anchor);
        $anchor = preg_replace('/ /', '-', $anchor);
        return $anchor;
    }
}
