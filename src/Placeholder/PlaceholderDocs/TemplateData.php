<?php
namespace nochso\WriteMe\Placeholder\PlaceholderDocs;

use BetterReflection\Reflection\ReflectionClass;
use nochso\Omni\Dot;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Markdown\Template as MarkdownTemplate;
use nochso\WriteMe\Placeholder\OptionList;
use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Yaml\Yaml;

class TemplateData extends MarkdownTemplate
{
    /**
     * @var \BetterReflection\Reflection\ReflectionClass[]
     */
    private $classes;
    /**
     * @var \nochso\WriteMe\Interfaces\Placeholder[]
     */
    private $placeholders;

    /**
     * @param \BetterReflection\Reflection\ReflectionClass[] $classes
     * @param \nochso\WriteMe\Interfaces\Placeholder[]       $placeholders
     */
    public function prepare(array $classes, array $placeholders)
    {
        $this->baseFolder = __DIR__ . '/Template';
        $this->classes = $classes;
        $this->placeholders = $placeholders;
    }

    /**
     * @return \nochso\WriteMe\Interfaces\Placeholder[]
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * @param \nochso\WriteMe\Interfaces\Placeholder $placeholder
     *
     * @return \BetterReflection\Reflection\ReflectionClass
     */
    public function getClassForPlaceholder(Placeholder $placeholder)
    {
        return $this->classes[$placeholder->getIdentifier()];
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
