<?php
namespace nochso\WriteMe;

use nochso\Omni\DotArray;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Placeholder\Frontmatter;

final class Converter implements Interfaces\Converter
{
    /**
     * @param \nochso\WriteMe\Document                 $document
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders
     */
    public function convert(Document $document, array $placeholders)
    {
        // Collect registered and frontmatter placeholders
        $placeholders = array_merge($placeholders, $this->extractFrontmatterPlaceholders($document));
        $this->applyPlaceholders($document, $placeholders);

        // Unescape left-over placeholders.
        $this->unescape($document);
    }

    /**
     * @param \nochso\WriteMe\Document                 $document
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders
     */
    public function applyPlaceholders(Document $document, array $placeholders)
    {
        foreach ($placeholders as $placeholder) {
            $placeholder->apply($document);
        }
    }

    /**
     * @param \nochso\WriteMe\Document $document
     *
     * @return \nochso\WriteMe\Interfaces\Placeholder[]
     */
    public function extractFrontmatterPlaceholders(Document $document)
    {
        $identifiers = self::extractIdentifiers($document);
        $frontmatter = $document->getFrontmatter();
        $placeholders = [];
        foreach ($identifiers as $identifier) {
            $value = DotArray::get($frontmatter, $identifier);
            $placeholders[] = new Frontmatter($identifier, $value);
        }
        return $placeholders;
    }

    /**
     * Replace a placeholder's identifier in a document.
     *
     * @param \nochso\WriteMe\Interfaces\Placeholder $placeholder The placeholder being searched for by its identifier.
     * @param string                                 $replacement The replacement string that replaces the placeholder.
     * @param \nochso\WriteMe\Document               $document    The document whose content will be searched and replaced.
     *
     * @return string
     */
    public static function replace(Placeholder $placeholder, $replacement, Document $document)
    {
        $quotedIdentifier = preg_quote($placeholder->getIdentifier(), '/');
        $pattern = '/(?<!@)(@(' . $quotedIdentifier . ')@)(?!@)/';
        $document->setContent(preg_replace($pattern, $replacement, $document->getContent()));
    }

    /**
     * @param \nochso\WriteMe\Document $document
     */
    public function unescape(Document $document)
    {
        $document->setContent(preg_replace('/@@([^@\r\n]+)@@/', '@\1@', $document->getContent()));
    }

    /**
     * @param \nochso\WriteMe\Document $document
     *
     * @return array
     */
    public function extractIdentifiers(Document $document)
    {
        $pattern = '/(?<!@)(@([^@\r\n]+)@)(?!@)/';
        if (preg_match_all($pattern, $document->getContent(), $matches)) {
            return $matches[2];
        }
        return [];
    }
}
