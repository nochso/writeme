<?php
namespace nochso\WriteMe\Reflection;

use BetterReflection\Reflection\ReflectionParameter;
use nochso\WriteMe\Markdown\DocBlock;

class Parameter
{
    /**
     * @var \BetterReflection\Reflection\ReflectionParameter
     */
    private $reflectionParameter;

    public function __construct(ReflectionParameter $reflectionParameter)
    {
        $this->reflectionParameter = $reflectionParameter;
    }

    /**
     * @return \BetterReflection\Reflection\ReflectionParameter
     */
    public function getReflectionParameter()
    {
        return $this->reflectionParameter;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->reflectionParameter->getName();
    }

    /**
     * @return string
     */
    public function getHints()
    {
        $hints = $this->reflectionParameter->getDocBlockTypeStrings();
        $hints[] = $this->reflectionParameter->getTypeHint();
        $hints = array_filter($hints, 'strlen');
        return implode('|', $hints);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $docBlock = new DocBlock($this->reflectionParameter->getDeclaringFunction()->getDocComment());
        /** @var \phpDocumentor\Reflection\DocBlock\Tag\ParamTag[] $paramTags */
        $paramTags = $docBlock->getTagsByName('param');
        foreach ($paramTags as $paramTag) {
            if ($paramTag->getVariableName() === '$' . $this->reflectionParameter->getName()) {
                return $paramTag->getDescription();
            }
        }
        return '';
    }
}
