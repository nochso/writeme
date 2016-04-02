<?php
namespace nochso\WriteMe\Placeholder;

use Nette\Utils\Finder;
use nochso\WriteMe\Document;
use nochso\WriteMe\Markdown\HeaderContent;
use nochso\WriteMe\Markdown\HeaderParser;

/**
 * Changelog fetches the most recent release notes from a CHANGELOG written in Markdown.
 *
 * This placeholder is intended for changelogs following the [keep-a-changelog](http://keepachangelog.com/) conventions.
 * However it should work for any Markdown formatted list of releases: each release is identified by a Markdown header.
 * What kind of header marks a release can be specified by the `changelog.release-level` option.
 */
class Changelog extends AbstractPlaceholder
{
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'changelog';
    }

    public function call(Call $call)
    {
        parent::call($call);
        
        $changelogPath = $this->findChangelog($call->getDocument());
        $changelog = Document::fromFile($changelogPath);
        $parser = new HeaderParser();
        $headerContentList = $parser->extractHeaderContents($changelog);
        $maxChanges = $this->options->getValue('changelog.max-changes');
        $releaseLevel = $this->options->getValue('changelog.release-level');
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
        $call->replace($latestChanges);
    }

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('changelog.max-changes', 'Maximum amount of releases to include.', 2),
            new Option('changelog.release-level', 'The header level that represents a release header.', 2),
            new Option('changelog.file', 'Filename of the CHANGELOG to extract releases from.', 'CHANGELOG.md'),
            new Option('changelog.search-depth', 'How deep the folders should be searched.', 2),
        ]);
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

    /**
     * @param \nochso\WriteMe\Document $document
     *
     * @return \SplFileInfo
     */
    private function findChangelog(Document $document)
    {
        $changelogName = $this->options->getValue('changelog.file');
        $searchDepth = $this->options->getValue('changelog.search-depth');
        $folder = dirname($document->getFilepath());
        $files = new \IteratorIterator(Finder::findFiles($changelogName)->from($folder)->limitDepth($searchDepth));
        $files->next();
        if (!$files->valid()) {
            throw new \RuntimeException(sprintf("Unable to find changelog '%s' in folder '%s'", $changelogName, $folder));
        }
        return $files->current();
    }
}
