<?php
namespace nochso\WriteMe\Placeholder\PlaceholderDocs;

use BetterReflection\Reflection\ReflectionClass;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Placeholder\AbstractPlaceholder;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Placeholder\Option;
use nochso\WriteMe\Placeholder\OptionList;

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
     * @var \nochso\WriteMe\Interfaces\Placeholder[]
     */
    private $placeholders;

    /**
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders
     */
    public function setPlaceholders(array $placeholders)
    {
        // Don't output documentation about this placeholder itself.
        // It's only for internal usage.
        $this->placeholders = array_filter($placeholders, function ($p) {
            return !$p instanceof self;
        });
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'placeholder-docs';
    }

    public function call(Call $call)
    {
        parent::call($call);

        $classes = [];
        foreach ($this->placeholders as $placeholder) {
            $classes[$placeholder->getIdentifier()] = ReflectionClass::createFromInstance($placeholder);
        }
        $template = new TemplateData();
        $template->setHeaderStartLevel($this->options->getValue('placeholder-docs.header-depth'));
        $template->prepare($classes, $this->placeholders);
        $docs = $template->render('full.php');
        $call->replace($docs);
    }

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('placeholder-docs.header-depth', 'Depth that headers start at', 2),
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
