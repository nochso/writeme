<?php
namespace nochso\WriteMe\Placeholder;

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
use phpDocumentor\Reflection\DocBlock;

/**
 * API does stuff.
 *
 * Second line.
 */
class API implements Placeholder
{
    /**
     * Override this using `api.visibility`.
     */
    const VISIBILITY_DEFAULT = ['public', 'protected'];

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
        $doApi = Converter::contains('api.advanced', $document);
        if (!$doSummary && !$doApi) {
            return;
        }

        $classes = $this->getClasses($document);
        if ($doSummary) {
            $apiSummary = $this->createAPISummary($classes, $document->getFrontmatter());
            Converter::replace('api.summary', $apiSummary, $document);
        }
        if ($doApi) {
            $api = $this->createAPI($classes, $document->getFrontmatter());
            Converter::replace('api.advanced', $api, $document);
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
        $prevNamespace = null;
        $visibility = $this->getVisibility($frontmatter);
        $api = 'This is a summary of namespaces, classes, interfaces, traits and ' . implode('/', array_keys($visibility)) . ' methods';
        foreach ($classes as $class) {
            $namespace = $class->getNamespaceName();
            if ($namespace !== $prevNamespace) {
                $api .= "\n- `N` `" . $class->getNamespaceName() . "`\n";
            }
            $prevNamespace = $namespace;

            $doc = new DocBlock($class->getDocComment());
            $api .= '    - `' . $this->getShortClassType($class) . '` ';
            $api .= $this->mergeNameWithShortDescription($class->getShortName(), $doc, '`' . $class->getShortName() . '`');
            $api .= "\n";
            foreach ($class->getImmediateMethods() as $method) {
                $methodDoc = new DocBlock($method->getDocComment());
                if (($method->isPublic() && isset($visibility['public']))
                    || ($method->isProtected() && isset($visibility['protected']))
                    || ($method->isPrivate() && isset($visibility['private']))
                ) {
                    $api .= '        - ';
                    $api .= $this->mergeNameWithShortDescription($method->getName(), $methodDoc, '`' . $method->getName() . '()`');
                    $api .= "\n";
                }
            }
        }

        return $api;
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionClass[] $classes
     * @param \nochso\WriteMe\Frontmatter                    $frontmatter
     *
     * @return string
     */
    private function createAPI($classes, $frontmatter)
    {
        $headerDepth = $frontmatter->get('api.header-depth', 3);
        $baseHeader = str_repeat('#', $headerDepth);
        $prevNamespace = null;
        $visibility = $this->getVisibility($frontmatter);
        $api = 'This is a summary of namespaces, classes, interfaces, traits and ' . implode('/', array_keys($visibility)) . ' methods.';
        foreach ($classes as $class) {
            $namespace = $class->getNamespaceName();
            if ($namespace !== $prevNamespace) {
                if ($prevNamespace === null) {
                    $api .= "\n";
                }
                $api .= "\n---------\n";
                $api .= "\n" . $baseHeader . ' Namespace `' . $class->getNamespaceName() . "`\n";
            }
            $prevNamespace = $namespace;

            $doc = new DocBlock($class->getDocComment());
            $api .= "\n" . $baseHeader . '# Class `' . $class->getShortName() . "`\n";
            $api .= $this->mergeNameWithShortDescription($class->getShortName(), $doc, '`' . $class->getShortName() . '`', true);
            if (strlen($doc->getLongDescription()) > 0) {
                $api .= "\n\n" . $doc->getLongDescription();
            }
            $api .= "\n";
            if (count($class->getImmediateMethods()) > 0) {
                $api .= $baseHeader . "## Methods\n";
                foreach ($class->getImmediateMethods() as $method) {
                    $methodDoc = new DocBlock($method->getDocComment());
                    if (($method->isPublic() && isset($visibility['public']))
                        || ($method->isProtected() && isset($visibility['protected']))
                        || ($method->isPrivate() && isset($visibility['private']))
                    ) {
                        $api .= '- ';
                        $api .= $this->mergeNameWithShortDescription($method->getName(), $methodDoc, '`' . $method->getName() . '()`');
                        $api .= "\n";
                    }
                }
            }
        }

        return $api;
    }

    private function mergeNameWithShortDescription($name, DocBlock $doc, $displayName = null, $nameOptional = false)
    {
        if ($displayName === null) {
            $displayName = $name;
        }
        $merged = $displayName;
        $words = explode(' ', trim($doc->getShortDescription()), 2);
        if (count($words) >= 2 && strtolower($words[0]) == strtolower($name)) {
            $merged .= ' ' . $words[1];
        } else {
            if ($nameOptional) {
                $merged = $doc->getShortDescription();
            } else {
                $merged .= ' ' . $doc->getShortDescription();
            }
        }
        return rtrim($merged);
    }

    private function getShortClassType(ReflectionClass $class)
    {
        $type = 'C';
        if ($class->isInterface()) {
            $type = 'I';
        } elseif ($class->isTrait()) {
            $type = 'T';
        }
        return $type;
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

    private function getVisibility(Frontmatter $frontmatter)
    {
        return array_flip($frontmatter->get('api.visibility', self::VISIBILITY_DEFAULT));
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
