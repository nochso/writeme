<?php
namespace nochso\WriteMe\Placeholder;

use Nette\Utils\Finder;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;

class Changelog implements Placeholder
{
    const FILE_NAME_DEFAULT = 'CHANGELOG.md';

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'changelog';
    }

    /**
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document)
    {
        $changelogPath = $this->findChangelog($document);
    }

    /**
     * @param \nochso\WriteMe\Document $document
     *
     * @return \SplFileInfo
     */
    private function findChangelog(Document $document)
    {
        $changelogName = $document->getFrontmatter()->get('changelog.file', self::FILE_NAME_DEFAULT);
        $folder = dirname($document->getFilepath());
        $files = new \IteratorIterator(Finder::findFiles($changelogName)->from($folder)->limitDepth(2));
        $files->next();
        if (!$files->valid()) {
            throw new \RuntimeException(sprintf("Unable to find changelog '%s' in folder '%s'", $changelogName, $folder));
        }
        return $files->current();
    }
}
