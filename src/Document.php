<?php
namespace nochso\WriteMe;

use nochso\Omni\Path;

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
     * @param string $filepath
     *
     * @return \nochso\WriteMe\Document
     */
    public static function fromFile($filepath)
    {
        return new self(file_get_contents($filepath), $filepath);
    }

    /**
     * @return \nochso\WriteMe\Frontmatter
     */
    public function getFrontmatter()
    {
        return $this->frontmatter;
    }

    /**
     * @param \nochso\WriteMe\Frontmatter $frontmatter
     */
    public function setFrontmatter(Frontmatter $frontmatter)
    {
        $this->frontmatter = $frontmatter;
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
     * @return string|null
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * @param string|null $overrideTarget
     *
     * @return string
     */
    public function getTargetFilepath($overrideTarget = null)
    {
        $target = $overrideTarget;
        // --target is optional. If empty, try the frontmatter key.
        if ($target === null) {
            $target = $this->frontmatter->get('target', null);
        }
        // Still empty: try replacing WRITEME* with README*
        if ($target === null) {
            // Only work with the actual file name
            $filename = basename($this->filepath);
            if (preg_match('/^(writeme)((\..+)?)/i', $filename, $matches)) {
                $name = $matches[1];
                $extension = $matches[2];
                if (strtoupper($name) === $name) {
                    $target = 'README' . $extension;
                } else {
                    $target = 'readme' . $extension;
                }
                $target = Path::combine(dirname($this->filepath), $target);
            }
        }
        if ($target === null) {
            throw new \RuntimeException(sprintf('Could not guess target file name from CLI option, frontmatter key "target" or source file name "%s".', $this->filepath));
        }
        return $target;
    }

    /**
     * @param string|null $overrideTarget
     *
     * @return string
     */
    public function saveTarget($overrideTarget = null)
    {
        $target = $this->getTargetFilepath($overrideTarget);
        file_put_contents($target, $this->content);
        return $target;
    }

    /**
     * saveRaw document including frontmatter.
     */
    public function saveRaw()
    {
        // Frontmatter implements __toString
        $raw = sprintf(
            "---\n%s\n---\n%s",
            $this->getFrontmatter(),
            $this->content
        );
        file_put_contents($this->getFilepath(), $raw);
    }
}
