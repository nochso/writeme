<?php
namespace nochso\WriteMe\Placeholder\API;

use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Nette\Utils\Finder;
use nochso\Omni\Path;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Frontmatter;
use nochso\WriteMe\Interfaces\Placeholder;

/**
 * API does stuff.
 *
 * Second line.
 */
class API implements Placeholder
{
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'api';
    }

    /**
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document)
    {
        $doSummary = Converter::contains('api.summary', $document);
        $doFullApi = Converter::contains('api.full', $document);
        if (!$doSummary && !$doFullApi) {
            return;
        }

        $classes = $this->getClasses($document);
        if ($doSummary) {
            $apiSummary = $this->createAPISummary($classes, $document->getFrontmatter());
            Converter::replace('api.summary', $apiSummary, $document);
        }
        if ($doFullApi) {
            $api = $this->createFullAPI($classes, $document->getFrontmatter());
            Converter::replace('api.full', $api, $document);
        }
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionClass[] $classes
     * @param \nochso\WriteMe\Frontmatter                    $frontmatter
     *
     * @return string
     */
    private function createAPISummary(array $classes, Frontmatter $frontmatter)
    {
        $template = new Template();
        $template->prepare($classes, $frontmatter);
        return $template->render('summary.php');
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionClass[] $classes
     * @param \nochso\WriteMe\Frontmatter                    $frontmatter
     *
     * @return string
     */
    private function createFullAPI(array $classes, Frontmatter $frontmatter)
    {
        $template = new Template();
        $template->prepare($classes, $frontmatter);
        return $template->render('full.php');
    }

    /**
     * @param \nochso\WriteMe\Document $doc
     *
     * @return \Nette\Utils\Finder
     */
    private function getFiles(Document $doc)
    {
        $frontmatter = $doc->getFrontmatter();
        $findFiles = $frontmatter->get('api.file', ['*.php']);
        $fromFolders = $frontmatter->get('api.from', ['.']);
        $folderExclude = $frontmatter->get('api.folder-exclude', ['vendor', 'test', 'tests']);

        $docPath = $doc->getFilepath();
        // Make folder paths relative to the folder of the WRITEME file in case CWD differs.
        if ($docPath !== null) {
            $fromFolders = $this->makeFoldersRelativeToFile($docPath, $fromFolders);
            $folderExclude = $this->makeFoldersRelativeToFile($docPath, $folderExclude);
        }
        $files = Finder::findFiles($findFiles)
            ->from($fromFolders)
            ->exclude($folderExclude);
        return $files;
    }

    /**
     * @param string $filepath
     * @param array  $folders
     *
     * @return array
     */
    private function makeFoldersRelativeToFile($filepath, $folders)
    {
        $fileFolder = dirname($filepath);
        if ($fileFolder === '.') {
            $fileFolder = '';
        }
        $combiner = function ($path) use ($fileFolder) {
            if (!Path::isAbsolute($path)) {
                return Path::combine($fileFolder, $path);
            }
            return $path;
        };
        return array_map($combiner, $folders);
    }

    /**
     * @param \nochso\WriteMe\Document $document
     *
     * @return \BetterReflection\Reflection\ReflectionClass[]
     */
    private function getClasses(Document $document)
    {
        $files = $this->getFiles($document);
        $singleLocators = [];
        foreach ($files as $file) {
            $singleLocators[] = new SingleFileSourceLocator($file->getPathname());
        }
        $reflector = new ClassReflector(new AggregateSourceLocator($singleLocators));
        $classes = $reflector->getAllClasses();
        usort($classes, function (ReflectionClass $a, ReflectionClass $b) {
            $ans = $a->getNamespaceName();
            $bns = $b->getNamespaceName();
            if ($ans === $bns) {
                return strnatcmp($a->getShortName(), $b->getShortName());
            }
            return strnatcmp($ans, $bns);
        });
        return $classes;
    }
}
