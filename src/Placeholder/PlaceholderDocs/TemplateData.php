<?php
namespace nochso\WriteMe\Placeholder\PlaceholderDocs;

use BetterReflection\Reflection\ReflectionClass;
use nochso\Omni\Dot;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Markdown\Template as MarkdownTemplate;
use nochso\WriteMe\Placeholder\OptionList;
use nochso\WriteMe\Placeholder\PlaceholderCollection;
use nochso\WriteMe\Reflection\DocBlock;
use Symfony\Component\Yaml\Yaml;

class TemplateData extends MarkdownTemplate
{
    /**
     * @var \BetterReflection\Reflection\ReflectionClass[]
     */
    private $classes;
    /**
     * @var \nochso\WriteMe\Placeholder\PlaceholderCollection
     */
    private $placeholders;

    /**
     * @param \BetterReflection\Reflection\ReflectionClass[]    $classes
     * @param \nochso\WriteMe\Placeholder\PlaceholderCollection $placeholders
     */
    public function prepare(array $classes, PlaceholderCollection $placeholders)
    {
        $this->baseFolder = __DIR__ . '/Template';
        $this->classes = $classes;
        $this->placeholders = $placeholders;
    }

    /**
     * @return \nochso\WriteMe\Interfaces\Placeholder[]
     */
    public function getRelevantPlaceholders()
    {
        $placeholders = [];
        foreach ($this->placeholders->toArray() as $placeholder) {
            if (!$placeholder instanceof PlaceholderDocs) {
                $placeholders[] = $placeholder;
            }
        }
        return $placeholders;
    }

    /**
     * @param \nochso\WriteMe\Interfaces\Placeholder $placeholder
     *
     * @return \nochso\WriteMe\Reflection\Method[]
     */
    public function getMethodsForPlaceholder(Placeholder $placeholder)
    {
        return $this->placeholders->getMethodsForPlaceholder($placeholder);
    }

    /**
     * @param \nochso\WriteMe\Interfaces\Placeholder $placeholder
     *
     * @return \BetterReflection\Reflection\ReflectionClass
     */
    public function getClassForPlaceholder(Placeholder $placeholder)
    {
        return $this->classes[get_class($placeholder)];
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
     * getOptionListYaml returns a YAML dump of default options.
     *
     * @param \nochso\WriteMe\Placeholder\OptionList $optionList
     *
     * @return string
     */
    public function getOptionListYaml(OptionList $optionList)
    {
        $data = [];
        foreach ($optionList->getOptions() as $option) {
            Dot::set($data, $option->getPath(), $option->getDefault());
        }
        return Yaml::dump($data);
    }
}
