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
     * @var \nochso\WriteMe\Interfaces\Placeholder[] Class name => Placeholder object
     */
    private $placeholderMap = [];
    /**
     * @var \nochso\WriteMe\Placeholder\Method[][] dotted method name => array of Method objects
     */
    private $methods = [];

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
        $className = get_class($placeholder);
        $this->placeholderMap[$className] = $placeholder;
        $methods = (new MethodFactory())->createFromPlaceholder($placeholder);
        foreach ($methods as $method) {
            $this->methods[$method->getDotName()][] = $method;
        }
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
     * getMethodsForCall by Call method name and priority.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     *
     * @return \nochso\WriteMe\Placeholder\Method[] A list of matching methods of this collection.
     */
    public function getMethodsForCall(Call $call)
    {
        $name = $call->getDotName();
        if (!isset($this->methods[$name])) {
            $name = Method::WILDCARD_METHOD_NAME;
        }
        if (!isset($this->methods[$name])) {
            return [];
        }
        $methods = [];
        foreach ($this->methods[$name] as $method) {
            if ($method->hasPriorityOfCall($call)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * @param \nochso\WriteMe\Interfaces\Placeholder $placeholder
     *
     * @return \nochso\WriteMe\Placeholder\Method[] Methods belonging to a placeholder.
     */
    public function getMethodsForPlaceholder(Placeholder $placeholder)
    {
        $methods = [];
        /** @var Method $method */
        foreach (Arrays::flatten($this->methods) as $method) {
            if ($method->getPlaceholder() === $placeholder) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * @param string $className
     *
     * @return \nochso\WriteMe\Interfaces\Placeholder
     */
    public function getPlaceholderByClassName($className)
    {
        if (!isset($this->placeholderMap[$className])) {
            return null;
        }
        return $this->placeholderMap[$className];
    }

    /**
     * toArray returns all placeholders.
     * 
     * @return \nochso\WriteMe\Interfaces\Placeholder[] Placeholder class name => Placeholder object
     */
    public function toArray()
    {
        return $this->placeholderMap;
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
