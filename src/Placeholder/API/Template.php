<?php
namespace nochso\WriteMe\Placeholder\API;

use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflection\ReflectionMethod;
use BetterReflection\Reflector\Exception\IdentifierNotFound;
use BetterReflection\Reflection\ReflectionParameter;
use nochso\Omni\Multiline;
use nochso\WriteMe\Frontmatter;
use nochso\WriteMe\Markdown\Template as MarkdownTemplate;
use nochso\WriteMe\Reflection\DocBlock;

/**
 * Template to help write custom API templates.
 *
 * By default only public and protected methods are returned. To override this,
 * use frontmatter key `api.visibility`.
 */
class Template extends MarkdownTemplate
{
    /**
     * Override this using `api.visibility`.
     */
    const VISIBILITY_DEFAULT = ['public', 'protected'];

    /**
     * @var \BetterReflection\Reflection\ReflectionClass[]
     */
    private $classes;
    /**
     * @var \BetterReflection\Reflection\ReflectionClass[][]
     */
    private $namespaceClassMap;
    /**
     * @var \nochso\WriteMe\Frontmatter
     */
    private $frontmatter;
    /**
     * @var string[]
     */
    private $visibilityMap;

    /**
     * @param \BetterReflection\Reflection\ReflectionClass[] $classes
     * @param \nochso\WriteMe\Frontmatter                    $frontmatter
     */
    public function prepare(array $classes, Frontmatter $frontmatter)
    {
        $this->baseFolder = __DIR__ . '/Template';
        $this->classes = $classes;
        $this->frontmatter = $frontmatter;
        $this->namespaceClassMap = [];
        foreach ($this->classes as $class) {
            $this->namespaceClassMap[$class->getNamespaceName()][] = $class;
        }
        $this->visibilityMap = array_flip($this->getVisibilityList());
        $this->setHeaderStartLevel($frontmatter->get('api.header-depth', 3));
    }

    /**
     * @return string[]
     */
    public function getVisibilityList()
    {
        return $this->frontmatter->get('api.visibility', self::VISIBILITY_DEFAULT);
    }

    /**
     * getNamespaces returns a list of all found namespace names.
     *
     * @return string[]
     */
    public function getNamespaces()
    {
        return array_keys($this->namespaceClassMap);
    }

    /**
     * getClassesInNamespace returns all classes in a namespace non-recursively.
     *
     * @param string $namespace
     *
     * @return \BetterReflection\Reflection\ReflectionClass[]
     */
    public function getClassesInNamespace($namespace)
    {
        return $this->namespaceClassMap[$namespace];
    }

    /**
     * getClasses returns all classes.
     *
     * @return \BetterReflection\Reflection\ReflectionClass[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * getVisibleMethods returns only the methods that are visible according to `api.visibility`.
     *
     * @param \BetterReflection\Reflection\ReflectionClass $class
     *
     * @return \BetterReflection\Reflection\ReflectionMethod[]
     */
    public function getVisibleMethods(ReflectionClass $class)
    {
        try {
            $methods = $class->getMethods();
        } catch (IdentifierNotFound $e) {
            $methods = $class->getImmediateMethods();
        }
        return array_filter($methods, [$this, 'isMethodVisible']);
    }

    /**
     * isMethodVisible returns true if the given method matches the visibility specified by `api.visibility`.
     *
     * @param \BetterReflection\Reflection\ReflectionMethod $method
     *
     * @return bool
     */
    public function isMethodVisible(ReflectionMethod $method)
    {
        return ($method->isPublic() && isset($this->visibilityMap['public']))
            || ($method->isProtected() && isset($this->visibilityMap['protected']))
            || ($method->isPrivate() && isset($this->visibilityMap['private']))
        ;
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionClass $class
     * @param string                                       $displayNameFormat
     *
     * @return string
     */
    public function mergeClassNameWithShortDescription(ReflectionClass $class, $displayNameFormat = '%s')
    {
        $doc = new DocBlock($class->getDocComment());
        $displayName = sprintf($displayNameFormat, $class->getShortName());
        return $this->mergeNameWithShortDescription($class->getShortName(), $doc, $displayName);
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionMethod $method
     * @param string                                        $displayNameFormat
     *
     * @return string
     */
    public function mergeMethodNameWithShortDescription(ReflectionMethod $method, $displayNameFormat = '%s')
    {
        $doc = new DocBlock($method->getDocComment());
        $displayName = sprintf($displayNameFormat, $method->getShortName());
        return $this->mergeNameWithShortDescription($method->getShortName(), $doc, $displayName);
    }

    /**
     * getShortClassType returns the abbreviated type of class: `C`, `I` or `T`.
     *
     * Class, Interface and Trait respectively.
     *
     * @param \BetterReflection\Reflection\ReflectionClass $class
     *
     * @return string
     */
    public function getShortClassType(ReflectionClass $class)
    {
        return $this->getLongClassType($class)[0];
    }

    /**
     * getLongClassType returns the type of class: `Class`, `Interface` or `Trait`.
     *
     * @param \BetterReflection\Reflection\ReflectionClass $class
     *
     * @return string
     */
    public function getLongClassType(ReflectionClass $class)
    {
        $type = 'Class';
        if ($class->isInterface()) {
            $type = 'Interface';
        } elseif ($class->isTrait()) {
            $type = 'Trait';
        }
        return $type;
    }

    public function getClassModifierSummary($class, $glue = ', ', $format = '%s', $optional = true)
    {
        $modifiers = $this->getClassModifiers($class);
        if (count($modifiers) === 0 && $optional) {
            return '';
        }
        return sprintf($format, implode($glue, $modifiers));
    }

    /**
     * getClassModifiers returns the modifiers of a class, e.g. `abstract`, `final`.
     *
     * @param \BetterReflection\Reflection\ReflectionClass $class
     *
     * @return array
     */
    public function getClassModifiers(ReflectionClass $class)
    {
        return \Reflection::getModifierNames($class->getModifiers());
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionMethod $method
     *
     * @return \phpDocumentor\Reflection\DocBlock
     */
    public function getMethodDocBlock(ReflectionMethod $method)
    {
        return new DocBlock($method->getDocComment());
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionClass $class
     *
     * @return \phpDocumentor\Reflection\DocBlock
     */
    public function getClassDocBlock(ReflectionClass $class)
    {
        return new DocBlock($class->getDocComment());
    }

    /**
     * @param \BetterReflection\Reflection\ReflectionMethod $method
     *
     * @return \phpDocumentor\Reflection\DocBlock\Tag
     */
    public function getReturnTag(ReflectionMethod $method)
    {
        $doc = $this->getMethodDocBlock($method);
        $returnTags = $doc->getTagsByName('return');
        if (count($returnTags) === 0) {
            return null;
        }
        return reset($returnTags);
    }

    public function formatParameter(ReflectionMethod $method, ReflectionParameter $parameter)
    {
        $code = '$' . $parameter->getName();
        $text = '';
        if ($parameter->isDefaultValueAvailable()) {
            if ($parameter->isDefaultValueConstant()) {
                $code .= ' = ' . $parameter->getDefaultValueConstantName();
            }
            $code .= ' = ' . $parameter->getDefaultValueAsString();
        }
        if ($parameter->isPassedByReference()) {
            $code = '&' . $code;
        }
        $doc = $this->getMethodDocBlock($method);
        $paramTags = $doc->getTagsByName('param');
        $type = 'mixed';
        /** @var \phpDocumentor\Reflection\DocBlock\Tag\ParamTag $paramTag */
        foreach ($paramTags as $paramTag) {
            if ($paramTag->getVariableName() === '$' . $parameter->getName() && $paramTag->getDescription() !== '') {
                $text .= ' &mdash; ' . $paramTag->getDescription();
                $type = $paramTag->getType();
            }
        }

        return sprintf('%d. `%s` &mdash; `%s` %s', $parameter->getPosition() + 1, $code, $type, $text);
    }

    public function formatMethod(ReflectionMethod $method)
    {
//        $params = [];
//        foreach ($method->getParameters() as $parameter) {
//            $params[] = '$' . $parameter->getName();
//        }
        $ml = Multiline::create($method->getLocatedSource()->getSource());
        return trim($ml[$method->getStartLine()-1]);
//        return sprintf('%s(%s)', $method->getName(), implo/de(', ', $params));
    }

    public function formatClass(ReflectionClass $class){
        $ml=Multiline::create($class->getLocatedSource()->getSource());
        return trim($ml[$class->getStartLine()-1]);
    }

    /**
     * godoc-style merging of a class or method name with the phpdoc short description.
     *
     * @param string                              $name
     * @param \nochso\WriteMe\Reflection\DocBlock $doc
     * @param string|null                         $displayName
     *
     * @return string
     */
    private function mergeNameWithShortDescription($name, DocBlock $doc, $displayName = null)
    {
        if ($displayName === null) {
            $displayName = $name;
        }
        $merged = $displayName;
        $words = explode(' ', trim($doc->getShortDescription()), 2);
        if (count($words) >= 2 && strtolower($words[0]) == strtolower($name)) {
            $merged .= ' ' . $words[1];
        } else {
            $merged .= ' ' . $doc->getShortDescription();
        }
        return rtrim($merged);
    }
}
