<?php
namespace nochso\WriteMe;

use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Placeholder\Frontmatter;

final class Converter implements Interfaces\Converter
{
    /**
     * @param \nochso\WriteMe\Document                 $document
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $registeredPlaceholders Key must be the placeholder's identifier.
     */
    public function convert(Document $document, array $registeredPlaceholders)
    {
        // Get potential front-matter replacements
        $frontMatterPlaceholders = $this->extractFrontmatterPlaceholders($document);
        // Merge with priority on registered placeholders
        $placeholders = $this->withMissing($registeredPlaceholders, $frontMatterPlaceholders);
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
     * @return \nochso\WriteMe\Interfaces\Placeholder[] Key is the dot-notation path
     */
    public function extractFrontmatterPlaceholders(Document $document)
    {
        $identifiers = self::extractIdentifiers($document);
        $frontmatter = $document->getFrontmatter();
        $placeholders = [];
        foreach ($identifiers as $identifier) {
            //$value = DotArray::get($frontmatter, $identifier);
            $value = $frontmatter->get($identifier);
            $placeholders[$identifier] = new Frontmatter($identifier, $value);
        }
        return $placeholders;
    }

    /**
     * Replace a placeholder's identifier in a document.
     *
     * @param string|\nochso\WriteMe\Interfaces\Placeholder $placeholder The placeholder being searched for by its identifier.
     * @param string                                        $replacement The replacement string that replaces the placeholder.
     * @param \nochso\WriteMe\Document                      $document    The document whose content will be searched and replaced.
     *
     * @return string
     */
    public static function replace($placeholder, $replacement, Document $document)
    {
        if ($placeholder instanceof Placeholder) {
            $placeholder = $placeholder->getIdentifier();
        }
        $quotedIdentifier = preg_quote($placeholder, '/');
        $pattern = '/(?<!@)(@(' . $quotedIdentifier . ')@)(?!@)/';
        $document->setContent(preg_replace($pattern, $replacement, $document->getContent()));
    }

    /**
     * @param string|\nochso\WriteMe\Interfaces\Placeholder $placeholder
     * @param \nochso\WriteMe\Document                      $document
     *
     * @return bool
     */
    public static function contains($placeholder, Document $document)
    {
        if ($placeholder instanceof Placeholder) {
            $placeholder = $placeholder->getIdentifier();
        }
        $quotedIdentifier = preg_quote($placeholder, '/');
        $pattern = '/(?<!@)(@(' . $quotedIdentifier . ')@)(?!@)/';
        return preg_match($pattern, $document->getContent()) === 1;
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

    /**
     * withMissing returns the original placeholders merged with additional placeholders.
     *
     * Original placeholders are preferred over additional ones with the same name.
     *
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $originalPlaceholders
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $additionalPlaceholders
     *
     * @return \nochso\WriteMe\Interfaces\Placeholder[]
     */
    private function withMissing(array $originalPlaceholders, $additionalPlaceholders)
    {
        $placeholders = $originalPlaceholders;
        foreach ($additionalPlaceholders as $key => $value) {
            if (!isset($placeholders[$key])) {
                $placeholders[$key] = $value;
            }
        }
        // Make sure custom frontmatter not related to placeholders is FIRST in the array.
        // This must be done to ensure that the TOC placeholder gets called LAST.
        uasort($placeholders, function ($a, $b) {
            $aIsFrontmatter = $a instanceof Frontmatter;
            $bIsFrontmatter = $b instanceof Frontmatter;
            if ($aIsFrontmatter xor $bIsFrontmatter) {
                return $aIsFrontmatter ? -1 : 1;
            }
            return 0;
        });
        return $placeholders;
    }
}
