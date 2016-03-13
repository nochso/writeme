<?php
namespace nochso\WriteMe\Interfaces;

use nochso\WriteMe\Document;

interface Placeholder
{
    /**
     * getIdentifier returns the default identifier to invoke this placeholder.
     *
     * For example an identifier `motd` would result in `@motd@` being
     * recognized as a placeholder. This **overrides** default behaviour of
     * freely defining placeholders in the frontmatter: `@motd@` will not be
     * replaced by its frontmatter content. Instead the placeholder needs to
     * modify the document itself in method `apply`.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Apply changes to the content of a document.
     *
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document);

    /**
     * getDefaultOptionList returns the list of **default** options that are used by this placeholder.
     *
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList();
}
