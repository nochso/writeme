<?php
namespace nochso\WriteMe;

class Document
{
    const FRONTMATTER_SEPARATOR = '---';

    /**
     * @var \nochso\WriteMe\Frontmatter
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
        $this->frontmatter = new Frontmatter();
        $this->content = $this->frontmatter->extract($content);
    }

    /**
     * @return \nochso\WriteMe\Frontmatter
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
}
