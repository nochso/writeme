<?php
namespace nochso\WriteMe\Markdown;

use nochso\Omni\DotArray;
use nochso\WriteMe\CLI\Stdio;
use nochso\WriteMe\Frontmatter;
use nochso\WriteMe\Placeholder\Option;
use nochso\WriteMe\Placeholder\OptionList;
use Symfony\Component\Yaml\Yaml;

/**
 * InteractiveTemplate renders a document template and collects frontmatter data interactively.
 */
class InteractiveTemplate extends Template
{
    /**
     * @var \nochso\WriteMe\CLI\Stdio
     */
    private $stdio;
    /**
     * @var \nochso\WriteMe\Frontmatter
     */
    private $frontmatter;
    /**
     * @var \nochso\WriteMe\Placeholder\PlaceholderCollection
     */
    private $placeholders;

    /**
     * @param \nochso\WriteMe\CLI\Stdio                         $stdio
     * @param \nochso\WriteMe\Placeholder\PlaceholderCollection $placeholders
     */
    public function __construct(Stdio $stdio, $placeholders)
    {
        $this->stdio = $stdio;
        $this->frontmatter = new Frontmatter([]);
        $this->baseFolder = __DIR__ . '/../../template/init';
        $this->placeholders = $placeholders;
    }

    /**
     * Ask user a question interactively and put the result in the frontmatter.
     *
     * @param string $dotPath
     * @param string $question
     * @param null   $default
     * @param null   $validator
     *
     * @return string|null
     */
    public function ask($dotPath, $question, $default = null, $validator = null)
    {
        $input = $this->stdio->ask($question, $default, $validator);
        $this->frontmatter->set($dotPath, $input);
        return $input;
    }

    /**
     * askForCustomPlaceholderOptions interactively.
     *
     * Placeholders know their default options. The user can override these defaults.
     * If the user does not override the defaults, nothing is added to the frontmatter.
     *
     * @param string $placeholderIdentifier Identifier of the placeholder.
     *
     * @return bool True on success. False when the placeholder is not known.
     */
    public function askForCustomPlaceholderOptionList($placeholderIdentifier)
    {
        $placeholder = $this->placeholders->getPlaceholderByClassName($placeholderIdentifier);
        if ($placeholder === null) {
            return false;
        }
        $defaults = $placeholder->getDefaultOptionList();
        $continue = true;
        while ($continue) {
            $numberPathMap = $this->showPlaceholderOptions($defaults);
            $continue = $this->askForCustomOption($numberPathMap, $defaults);
        }
        return true;
    }

    /**
     * @return \nochso\WriteMe\Frontmatter
     */
    public function getFrontmatter()
    {
        return $this->frontmatter;
    }

    /**
     * @return \nochso\WriteMe\CLI\Stdio
     */
    public function getStdio()
    {
        return $this->stdio;
    }

    /**
     * showPlaceholderOptions defaults and current values.
     *
     * @param \nochso\WriteMe\Placeholder\OptionList $defaults
     *
     * @return string[] Integer position => option path
     */
    private function showPlaceholderOptions(OptionList $defaults)
    {
        $i = 0;
        $numberPathMap = [];
        foreach ($defaults->getOptions() as $option) {
            // Remember the number for each option
            $numberPathMap[$i] = $option->getPath();
            $line = sprintf(
                '[%d] %s default: <<yellow>>%s<<reset>>',
                $i,
                $option->getPath(),
                $this->castToString($option->getDefault())
            );
            $currentValue = $this->frontmatter->get($option->getPath());
            if ($currentValue !== null) {
                $line = sprintf('%s user: <<green>>%s<<reset>>', $line, $this->castToString($currentValue));
            }
            $this->stdio->outln($line);
            $this->stdio->outln('  - ' . $option->getDescription());
            $i++;
        }
        $this->stdio->outln();
        return $numberPathMap;
    }

    /**
     * askForCustomOption to override frontmatter.
     *
     * @param string[]                               $numberPathMap
     * @param \nochso\WriteMe\Placeholder\OptionList $defaults
     *
     * @return bool True if you should continue asking. False if nothing was entered.
     */
    private function askForCustomOption($numberPathMap, OptionList $defaults)
    {
        $number = $this->stdio->ask('Enter the [number] of the option you want to change (leave empty to continue)', '');
        // Empty to continue
        if ($number === '') {
            return false;
        }
        // Ask again for a valid number
        if (!isset($numberPathMap[$number])) {
            return true;
        }
        $option = $defaults->getOption($numberPathMap[$number]);
        $currentValue = $this->frontmatter->get($option->getPath(), $option->getDefault());
        // Some options take a list of values
        if (is_array($currentValue)) {
            $this->askForCustomOptionArray($currentValue, $option);
        } else {
            // Otherwise ask for a single value
            $this->ask($option->getPath(), $option->getDescription(), $currentValue);
        }
        // Ask again for another option
        return true;
    }

    private function askForCustomOptionArray($currentValue, Option $option)
    {
        $da = new DotArray($currentValue);
        $this->stdio->outln($option->getPath());
        $this->stdio->outln($option->getDescription());

        $continue = true;
        while ($continue) {
            $this->stdio->displayList($da->flatten());
            $actions = ['delete', 'clear all', 'add', 'replace', 'skip'];
            $action = $this->stdio->chooseAction($actions);
            switch ($action) {
                case 'delete':
                    $index = $this->stdio->chooseFromList($da->flatten(), 'Item to delete', false);
                    if ($index !== null) {
                        $da->remove($index);
                    }
                    break;
                case 'clear all':
                    $da = new DotArray();
                    break;
                case 'add':
                    $newValue = $this->stdio->ask('Enter value of the new item');
                    $da = new DotArray(array_merge($da->getArray(), [$newValue]));
                    break;
                case 'replace':
                    $index = $this->stdio->chooseFromList($da->flatten(), 'Item to replace', false);
                    if ($index !== null) {
                        $da->set($index, $this->stdio->ask('Enter new value'));
                    }
                    break;
                case 'skip':
                    $continue = false;
                    break;
            }
        }
        $this->frontmatter->set($option->getPath(), $da->getArray());
    }

    /**
     * castToString returns a string representation even if it's an array.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function castToString($value)
    {
        if (is_array($value)) {
            return Yaml::dump($value, 0);
        }
        return (string) $value;
    }
}
