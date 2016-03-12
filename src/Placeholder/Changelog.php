<?php
namespace nochso\WriteMe\Placeholder;

use Nette\Utils\Finder;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Markdown\HeaderContent;
use nochso\WriteMe\Markdown\Parser;

class Changelog implements Placeholder
{
    const FILE_NAME_DEFAULT = 'CHANGELOG.md';
    const MAX_CHANGES_DEFAULT = 2;
    const RELEASE_LEVEL_DEFAULT = 2;

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
        $changelog = Document::fromFile($changelogPath);
        $parser = new Parser();
        $headerContentList = $parser->extractHeaderContents($changelog);
        $maxChanges = $document->getFrontmatter()->get('changelog.max-changes', self::MAX_CHANGES_DEFAULT);
        $releaseLevel = $document->getFrontmatter()->get('changelog.release-level', self::RELEASE_LEVEL_DEFAULT);
        $changes = 0;
        $latestChanges = '';
        /** @var HeaderContent $headerContent */
        foreach ($headerContentList->getHeaders() as $headerContent) {
            // This header marks a release
            if ($headerContent->getLevel() === $releaseLevel) {
                $changes++;
                // Stop if we reached the max amount of changes.
                if ($changes > $maxChanges) {
                    break;
                }
                $latestChanges .= $headerContent->toMarkdown() . "\n";
            }
            // Keep adding sub-HeaderContent if we're within a release
            if ($changes > 0 && $headerContent->getLevel() > $releaseLevel) {
                $latestChanges .= $headerContent->toMarkdown() . "\n";
            }
        }
        Converter::replace($this, $latestChanges, $document);
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
