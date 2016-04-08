<?php
namespace nochso\WriteMe\Placeholder;

use nochso\Omni\Arrays;
use nochso\Omni\Dot;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;

/**
 * PlaceholderCollection for storing and querying Placeholder objects.
 */
class PlaceholderCollection
{
    /**
     * @var \nochso\WriteMe\Interfaces\Placeholder[][] Identifier => array of Placeholders
     */
    private $placeholderMap = [];

    /**
     * Construct a new collection of Placeholder objects.
     *
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders Optional array of Placeholder objects. Defaults to
     *                                                               an empty array.
     */
    public function __construct(array $placeholders = [])
    {
        $this->addMany($placeholders);
    }

    /**
     * Add a single placeholder.
     * 
     * @param \nochso\WriteMe\Interfaces\Placeholder $placeholder
     */
    public function add(Placeholder $placeholder)
    {
        if (!isset($this->placeholderMap[$placeholder->getIdentifier()])) {
            $this->placeholderMap[$placeholder->getIdentifier()] = [];
        }
        $this->placeholderMap[$placeholder->getIdentifier()][] = $placeholder;
    }

    /**
     * Add an array of Placeholder objects.
     * 
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders
     */
    public function addMany(array $placeholders)
    {
        foreach ($placeholders as $placeholder) {
            $this->add($placeholder);
        }
    }

    /**
     * preparePlaceholders with the document they're going to be working with.
     *
     * Should be called once before any Calls take place.
     *
     * @param \nochso\WriteMe\Document $document
     */
    public function preparePlaceholders(Document $document)
    {
        foreach ($this->toArray() as $placeholder) {
            $placeholder->prepare($document);
        }
    }

    /**
     * getPlaceholdersForCall returns all placeholders that could potentially handle this call.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     *
     * @return \nochso\WriteMe\Placeholder\PlaceholderCollection
     */
    public function getPlaceholdersForCall(Call $call)
    {
        // Catch-all placeholders as fallback 
        $identifier = Placeholder::IDENTIFIER_MATCH_ALL;
        // Use a more specific placeholder if possible
        if (isset($this->placeholderMap[$call->getIdentifier()])) {
            $identifier = $call->getIdentifier();
        }
        $placeholders = Dot::get($this->placeholderMap, $identifier, []);
        $priorityPlaceholders = [];
        foreach ($placeholders as $placeholder) {
            if (in_array($call->getPriority(), $placeholder->getCallPriorities())) {
                $priorityPlaceholders[] = $placeholder;
            }
        }
        return new self($priorityPlaceholders);
    }

    /**
     * toArray returns all placeholders.
     * 
     * @return \nochso\WriteMe\Interfaces\Placeholder[]
     */
    public function toArray()
    {
        return Arrays::flatten($this->placeholderMap);
    }

    /**
     * getPriorities of all placeholders sorted from lowest to highest without duplicates.
     *
     * @return int[]
     */
    public function getPriorities()
    {
        $priorities = [];
        foreach ($this->toArray() as $placeholder) {
            $priorities = array_merge($priorities, $placeholder->getCallPriorities());
        }
        $priorities = array_unique($priorities);
        sort($priorities, SORT_NUMERIC);
        return $priorities;
    }
}
