<?php
namespace nochso\WriteMe\Placeholder\API;

use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Nette\Utils\Finder;
use nochso\Omni\Path;
use nochso\WriteMe\Document;
use nochso\WriteMe\Frontmatter;
use nochso\WriteMe\Placeholder\AbstractPlaceholder;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\Option;
use nochso\WriteMe\Placeholder\OptionList;

/**
 * API creates documentation from your PHP code.
 *
 * By default it will search for all `*.php` files in your project excluding the Composer `vendor` and `test*` folders.
 *
 * Currently there are two placeholders, each with a different template:
 *
 * - `@api.summary@`
 *     - Indented list of namespaces, classes and methods including the first line of PHPDocs.
 * - `@api.full@`
 *     - Verbose documentation for each class and methods.
 */
class API extends AbstractPlaceholder
{
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'api';
    }

    public function call(Call $call)
    {
        parent::call($call);
        if ($call->getMethod() !== 'summary' && $call->getMethod() !== 'full') {
            $call->replace('');
        }

        $classes = $this->getClasses($call->getDocument());
        if ($call->getMethod() === 'summary') {
            $apiSummary = $this->createAPISummary($classes, $call->getDocument()->getFrontmatter());
            $call->replace($apiSummary);
        }
        if ($call->getMethod() === 'full') {
            $api = $this->createFullAPI($classes, $call->getDocument()->getFrontmatter());
            $call->replace($api);
        }
    }

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('api.file', 'List of file patterns to parse.', ['*.php']),
            new Option('api.from', 'List of folders to search files in.', ['.']),
            new Option('api.folder-exclude', 'List of folders to exclude from the search.', ['vendor', 'test', 'tests']),
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
        $findFiles = $this->options->getValue('api.file');
        $fromFolders = $this->options->getValue('api.from');
        $folderExclude = $this->options->getValue('api.folder-exclude');

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
