<?php
namespace nochso\WriteMe\Placeholder;

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
        $setext = '(?:^|[\r\n])([^\r\n]+)[\r\n]{1,2}([=-]+)(?:$|[\r\n])';
        $atx = '(?:^|[\r\n])(#+)(?:\s*)(.+)(?:\s*#*\s*)(?:$|[\r\n])';
        $pattern = '/(?:' . $setext . '|' . $atx . ')/';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $count = count($match);
                if ($count === 5) {
                    $elements[] = [strlen($match[3]), trim($match[4])];
                } elseif ($count === 3) {
                    $level = Strings::startsWith($match[2], '=') ? 1 : 2;
                    $elements[] = [$level, trim($match[1])];
                }
            }
        }

        $toc = '';
        foreach ($elements as $element) {
            $indent = str_repeat('    ', $element[0] - 1);
            $toc .= $indent . '- [' . $element[1] . '](#' . $this->getAnchor($element[1]) . ")\n";
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
