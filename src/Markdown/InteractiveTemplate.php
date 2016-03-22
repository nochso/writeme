<?php
namespace nochso\WriteMe\Markdown;

use nochso\WriteMe\CLI\Stdio;
use nochso\WriteMe\Frontmatter;

/**
 * InteractiveTemplate.
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
     * @var \nochso\WriteMe\Interfaces\Placeholder[]
     */
    private $placeholders;

    /**
     * @param \nochso\WriteMe\CLI\Stdio                $stdio
     * @param \nochso\WriteMe\Interfaces\Placeholder[] $placeholders
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
     * @return \nochso\WriteMe\Frontmatter
     */
    public function getFrontmatter()
    {
        return $this->frontmatter;
    }
}
