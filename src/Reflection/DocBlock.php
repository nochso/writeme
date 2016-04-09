<?php
namespace nochso\WriteMe\Reflection;

use nochso\Omni\Multiline;
use nochso\Omni\Strings;

/**
 * DocBlock object with support for Markdown formatted PHPDocs.
 */
class DocBlock extends \phpDocumentor\Reflection\DocBlock
{
    /**
     * @var int Current position in the lines of a DocBlock
     */
    private $position;

    /**
     * Splits the DocBlock into a template marker, summary, description and
     * block of tags.
     *
     * @param string $comment Comment to split into the sub-parts.
     *
     * @return string[] containing the template marker (if any), summary,
     *                  description and a string containing the tags.
     */
    protected function splitDocBlock($comment)
    {
        if (strpos($comment, '@') === 0) {
            return ['', '', '', $comment];
        }
        $lines = Multiline::create($comment);
        $this->position = 0;
        $marker = $this->extractTemplateMarker($lines);
        $summary = $this->extractShortDescription($lines);
        $description = $this->extractLongDescription($lines);
        $tags = $this->extractTags($lines);
        return [$marker, $summary, $description, $tags];
    }

    /**
     * @param \nochso\Omni\Multiline $lines
     *
     * @return string
     */
    private function extractTemplateMarker(Multiline $lines)
    {
        $marker = '';
        $firstLine = rtrim($lines->first());
        if ($firstLine === '#@+' || $firstLine === '#@-') {
            $marker = $firstLine;
            $this->position++;
        }
        return $marker;
    }

    /**
     * @param \nochso\Omni\Multiline $lines
     *
     * @return string
     */
    private function extractShortDescription(Multiline $lines)
    {
        $summary = new Multiline();
        $summary->setEol((string) $lines->getEol());
        for (; $this->position < count($lines); $this->position++) {
            $line = $lines[$this->position];
            if (Strings::startsWith($line, '@')) {
                break;
            }
            if (trim($line) === '') {
                $this->position++;
                break;
            }
            $summary->add($line);
            if (Strings::endsWith($line, '.')) {
                $this->position++;
                break;
            }
        }
        return (string) $summary;
    }

    /**
     * @param \nochso\Omni\Multiline $lines
     *
     * @return string
     */
    private function extractLongDescription(Multiline $lines)
    {
        $description = new Multiline();
        $description->setEol((string) $lines->getEol());
        $isFenced = false;
        for (; $this->position < count($lines); $this->position++) {
            $line = $lines[$this->position];
            if (preg_match('/^```(?!`)/', $line) === 1) {
                $isFenced = !$isFenced;
            }
            if (!$isFenced && Strings::startsWith($line, '@')) {
                break;
            }
            $description->add($line);
        }
        return (string) $description;
    }

    /**
     * @param \nochso\Omni\Multiline $lines
     *
     * @return string
     */
    private function extractTags(Multiline $lines)
    {
        $tags = new Multiline(array_slice($lines->toArray(), $this->position));
        $tags->setEol((string) $lines->getEol());
        return (string) $tags;
    }
}
