<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Interfaces\Placeholder;

class Frontmatter extends AbstractPlaceholder
{
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return Placeholder::IDENTIFIER_MATCH_ALL;
    }

    public function call(Call $call)
    {
        $path = $call->getIdentifier();
        if ($call->getMethod() !== null) {
            $path .= '.' . $call->getMethod();
        }
        $value = $call->getDocument()->getFrontmatter()->get($path);
        if ($value !== null) {
            $call->replace($value);
        }
    }

    /**
     * getCallPriorities defining when a Placeholder is supposed to be called between multiple passes.
     *
     * @return int[]
     */
    public function getCallPriorities()
    {
        return [self::PRIORITY_FIRST];
    }
}
