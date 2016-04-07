<?php
namespace nochso\WriteMe;

use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\PlaceholderCollection;

final class Converter
{
    /**
     * Convert a document using a collection of placeholders.
     *
     * @param \nochso\WriteMe\Document                          $document
     * @param \nochso\WriteMe\Placeholder\PlaceholderCollection $placeholders
     */
    public function convert(Document $document, PlaceholderCollection $placeholders)
    {
        $this->applyPlaceholders($document, $placeholders);
        // Unescape left-over placeholders.
        $document->setContent($this->unescape($document->getContent()));
    }

    /**
     * applyPlaceholders to a Document object.
     *
     * @param \nochso\WriteMe\Document                          $document     The document to modify.
     * @param \nochso\WriteMe\Placeholder\PlaceholderCollection $placeholders The placeholders used to modify the
     *                                                                        document.
     */
    private function applyPlaceholders(Document $document, PlaceholderCollection $placeholders)
    {
        $priorities = $placeholders->getPriorities();
        foreach ($priorities as $priority) {
            $this->applyPlaceholdersAtPriority($document, $priority, $placeholders);
        }
    }

    /**
     * applyPlaceholdersAtPriority by calling only the placeholders that are relevant at a certain priority.
     *
     * @param Document                                          $document     The document to modify.
     * @param int                                               $priority     The priority stage to consider.
     *                                                                        Placeholders are only called when they've
     *                                                                        listed that priority. See
     *                                                                        `Placeholder::getCallPriorities()`
     * @param \nochso\WriteMe\Placeholder\PlaceholderCollection $placeholders
     */
    private function applyPlaceholdersAtPriority(Document $document, $priority, PlaceholderCollection $placeholders)
    {
        $offset = 0;
        $call = Call::extractFirstCall($document, $priority, $offset);
        while ($call !== null) {
            $callPlaceholders = $placeholders->getPlaceholdersForCall($call);
            $isReplaced = false;
            foreach ($callPlaceholders->toArray() as $placeholder) {
                $placeholder->call($call);
                if ($call->isReplaced()) {
                    $isReplaced = true;
                    break;
                }
            }
            if ($isReplaced) {
                // Anything could have changed in content. Start from the beginning.
                $offset = 0;
            } else {
                // The call was skipped by all placeholders. Ignore it at this priority.
                $offset = $call->getEndPositionOfRawCall();
            }
            $call = Call::extractFirstCall($document, $priority, $offset);
        }
    }

    /**
     * @param string $content
     * 
     * @return string
     */
    public function unescape($content)
    {
        // First turn \@foo\@ into @foo@
        $unescaped = preg_replace(Call::REGEX_ESCAPED, '@$2@', $content);
        // Then allow writing \\@foo\\@ to achieve \@foo\@ in final output
        $unescaped = preg_replace(Call::REGEX_ESCAPED_ESCAPED, '\\\\@$2\\\\@', $unescaped);
        return $unescaped;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function escape($content)
    {
        // First escape an already escaped placeholder
        $escapedContent = preg_replace(Call::REGEX_ESCAPED, '\\\\\\\\@$2\\\\\\\\@', $content);
        // Escape not-yet-escaped placeholder
        $escapedContent = preg_replace(Call::REGEX, '\\\\@$2\\\\@', $escapedContent);
        return $escapedContent;
    }
}
