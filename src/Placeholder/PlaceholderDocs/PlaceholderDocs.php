<?php
namespace nochso\WriteMe\Placeholder\PlaceholderDocs;

use BetterReflection\Reflection\ReflectionClass;
use nochso\WriteMe\Converter;
use nochso\WriteMe\Document;
use nochso\WriteMe\Placeholder\AbstractPlaceholder;
use nochso\WriteMe\Placeholder\Option;
use nochso\WriteMe\Placeholder\OptionList;

/**
 * PlaceholderDocs creates documentation for registered placeholders.
 *
 * This includes the PHPDoc for the classes and their supported options.
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
        $this->placeholders = $placeholders;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'placeholder-docs';
    }

    /**
     * @param \nochso\WriteMe\Document $document
     */
    public function apply(Document $document)
    {
        parent::apply($document);

        $classes = [];
        foreach ($this->placeholders as $placeholder) {
            $classes[$placeholder->getIdentifier()] = ReflectionClass::createFromInstance($placeholder);
        }
        $template = new TemplateData();
        $template->setHeaderStartLevel($this->options->getValue('placeholder-docs.header-depth'));
        $template->prepare($classes, $this->placeholders);
        $docs = $template->render('full.php');
        Converter::replace($this, $docs, $document);
    }

    /**
     * @return \nochso\WriteMe\Placeholder\OptionList
     */
    public function getDefaultOptionList()
    {
        return new OptionList([
            new Option('placeholder-docs.header-depth', 'Depth that headers start at', 3),
        ]);
    }
}
