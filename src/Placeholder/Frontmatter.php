<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Interfaces\Placeholder;

/**
 * Frontmatter placeholders return values defined in the frontmatter.
 *
 * You can define any kind of structure as long as it doesn't collide with the name of any other available placeholder:
 *
 * ```yaml
 * ---
 * greet: Hello
 * user:
 *     name: [Annyong, Tobias]
 * key.has.dots: yes
 * ---
 * @greet@ @user.name.0@!
 * key has dots: @key\.has\.dots@
 * ```
 *
 * Frontmatter values are accessed using dot-notation, resulting in this output:
 *
 * ```markdown
 * Hello Annyong!
 * key has dots: yes
 * ```
 *
 * Using dots in the keys themselves is possible by escaping them with backslashes. See the `Dot` class provided by
 * [nochso/omni](https://github.com/nochso/omni).
 */
class Frontmatter extends AbstractPlaceholder
{
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return Placeholder::IDENTIFIER_MATCH_ALL;
    }

    public function wildcard(Call $call)
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
