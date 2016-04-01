<?php
namespace nochso\WriteMe\Interfaces;

use nochso\WriteMe\Placeholder\Call;

interface Placeholder
{
    /**
     * getIdentifier returns the default identifier to invoke this placeholder.
     *
     * For example an identifier `motd` would result in `@motd@` being
     * recognized as a placeholder. This **overrides** default behaviour of
     * freely defining placeholders in the frontmatter: `@motd@` will not be
     * replaced by its frontmatter content. Instead the placeholder needs to
     * modify the document itself when being `call`ed.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Call a method on the placeholder and expect it to modify the document.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call Contains an optional method name and parameters
     */
    public function call(Call $call);

    /**
     * getDefaultOptionList returns the list of **default** options that are used by this placeholder.
     *
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList();
}
