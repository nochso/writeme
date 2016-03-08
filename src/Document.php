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
     * @var string
     */
    private $filepath;

    /**
     * @param string      $content
     * @param string|null $filepath
     */
    public function __construct($content, $filepath = null)
    {
        $this->frontmatter = new Frontmatter();
        $this->content = $this->frontmatter->extract($content);
        $this->filepath = $filepath;
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

    /**
     * @param string $filepath
     *
     * @return \nochso\WriteMe\Document
     */
    public static function fromFile($filepath)
    {
        return new self(file_get_contents($filepath), $filepath);
    }

    /**
     * @return string|null
     */
    public function getFilepath()
    {
        return $this->filepath;
    }
}
