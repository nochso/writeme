<?php
namespace nochso\WriteMe\Interfaces;

use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\Call;

/**
 * Placeholder interface common to all placeholders that can be called/applied
 * to a document.
 */
interface Placeholder
{
    /**
     * Priority for placeholders that are independent of others.
     */
    const PRIORITY_FIRST = 0;
    /**
     * Priority for placeholders that need to be called after others have been
     * called.
     */
    const PRIORITY_LAST = 1000;

    /**
     * Special identifier for placeholders that can react to any identifier.
     *
     * @see \nochso\WriteMe\Placeholder\Frontmatter
     */
    const IDENTIFIER_MATCH_ALL = '*';

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
     * Prepare a placeholder before calling its methods.
     *
     * @param \nochso\WriteMe\Document $document
     */
    public function prepare(Document $document);

    /**
     * getDefaultOptionList returns the list of **default** options that are
     * used by this placeholder.
     *
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList();

    /**
     * getCallPriorities defining when a Placeholder is supposed to be called
     * between multiple passes.
     *
     * Usually one of the `PRIORITY_*` constants defined by this interface will
     * suffice. However any integer can be used.
     *
     * @return int[]
     */
    public function getCallPriorities();
}
