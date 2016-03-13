<?php
namespace nochso\WriteMe\Placeholder;

/**
 * OptionList contains all options by a Placeholder.
 *
 * An OptionList should be prepare()ed with the frontmatter to override the default values.
 */
class OptionList
{
    /**
     * @var \nochso\WriteMe\Placeholder\Option[]
     */
    private $options = [];

    /**
     * @param \nochso\WriteMe\Placeholder\Option[] $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $option) {
            $this->options[$option->getPath()] = $option;
        }
    }

    /**
     * Prepare options by overriding defaults with frontmatter values.
     *
     * @param \nochso\WriteMe\Frontmatter $frontmatter
     */
    public function prepare(\nochso\WriteMe\Frontmatter $frontmatter)
    {
        foreach ($this->options as $option) {
            $option->setValue($frontmatter->get($option->getPath(), $option->getDefault()));
        }
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function getValue($path)
    {
        return $this->getOption($path)->getValue();
    }

    /**
     * @param string $path
     *
     * @return \nochso\WriteMe\Placeholder\Option
     */
    public function getOption($path)
    {
        if (!isset($this->options[$path])) {
            throw new \RuntimeException(sprintf("Could not find option '%s'.", $path));
        }
        return $this->options[$path];
    }

    /**
     * @return \nochso\WriteMe\Placeholder\Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }
}
