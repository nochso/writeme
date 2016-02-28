<?php
namespace nochso\WriteMe;

use nochso\Omni\EOL;
use Symfony\Component\Yaml\Yaml;

class Document
{
    const FRONTMATTER_SEPARATOR = '---';

    /**
     * @var array
     */
    private $frontmatter;
    /**
     * @var string
     */
    private $content = '';

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->extract($content);
    }

    /**
     * @return array
     */
    public function getFrontmatter()
    {
        return $this->frontmatter;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param $content
     */
    private function extract($content)
    {
        $sections = explode(self::FRONTMATTER_SEPARATOR, $content);
        $count = count($sections);
        $frontmatter = [];
        if ($count >= 2) {
            $frontmatter = $sections[1];
        }
        if ($count >= 3) {
            // Implode all remaining sections so `---` can be used in the content.
            $this->content = implode(self::FRONTMATTER_SEPARATOR, array_slice($sections, 2));
            // Trim only the first line ending
            $eol = (string) EOL::detectDefault($this->content);
            $len = strlen($eol);
            if (substr($this->content, 0, $len) === $eol) {
                $this->content = substr($this->content, $len);
            }
        }
        if (is_string($frontmatter)) {
            $frontmatter = Yaml::parse($frontmatter, true);
        }
        $this->frontmatter = $frontmatter;
    }
}
