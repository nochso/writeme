<?php
namespace nochso\WriteMe\Placeholder\PlaceholderDocs;

use BetterReflection\Reflection\ReflectionClass;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Placeholder\AbstractPlaceholder;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\Option;
use nochso\WriteMe\Placeholder\OptionList;
use nochso\WriteMe\Placeholder\PlaceholderCollection;

/**
 * PlaceholderDocs creates documentation for registered placeholders.
 *
 * This includes the PHPDoc for the classes and their supported options.
 *
 * @internal
 */
class PlaceholderDocs extends AbstractPlaceholder
{
    /**
     * @var \nochso\WriteMe\Placeholder\PlaceholderCollection
     */
    private $placeholders;

    /**
     * @param \nochso\WriteMe\Placeholder\PlaceholderCollection $placeholders
     */
    public function setPlaceholderCollection(PlaceholderCollection $placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'placeholder-docs';
    }

    public function writemePlaceholderDocs(Call $call)
    {
        $classes = [];
        foreach ($this->placeholders->toArray() as $placeholder) {
            $classes[get_class($placeholder)] = ReflectionClass::createFromInstance($placeholder);
        }
        $template = new TemplateData();
        $template->setHeaderStartLevel($this->options->getValue('placeholder-docs.header-depth'));
        $template->prepare($classes, $this->placeholders);
        $docs = $template->render('full.php');
        $docs = (new Converter())->escape($docs);
        $call->replace($docs);
    }

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('placeholder-docs.header-depth', 'Depth that headers start at', 1),
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
}
