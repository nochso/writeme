<?php
namespace nochso\WriteMe;

use nochso\Omni\Dot;
use nochso\Omni\Multiline;
use Symfony\Component\Yaml\Yaml;

/**
 * Frontmatter provides the data from a frontmatter file string.
 */
class Frontmatter
{
    const FRONTMATTER_SEPARATOR = '---';

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get frontmatter value using dot.notation as a key/path.
     *
     * @param string     $dotPath Dot notation path to look up
     * @param null|mixed $default Default value to return if the element doesn't exist. Optional, defaults to NULL.
     *
     * @return mixed
     */
    public function get($dotPath, $default = null)
    {
        return Dot::get($this->data, $dotPath, $default);
    }

    /**
     * Set a frontmatter value using dot.notation as a key/path.
     *
     * @param string $dotPath Dot notation path to set/replace a value
     * @param mixed  $value
     */
    public function set($dotPath, $value)
    {
        Dot::set($this->data, $dotPath, $value);
    }

    /**
     * @return array The data extracted from the document frontmatter.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Extract frontmatter from a raw document and return the remaining document content.
     *
     * @param string $input Raw file content
     *
     * @return string The remaining document content after extracting the frontmatter into this object.
     */
    public function extract($input)
    {
        $this->data = [];
        $lines = Multiline::create($input);
        // Frontmatter is missing: assume everything is content.
        if ($lines->first() !== self::FRONTMATTER_SEPARATOR) {
            return $input;
        }
        $frontmatterEnd = $this->findFrontmatterEndPosition($lines);
        $rawFrontmatter = implode($lines->getEol(), array_slice($lines->toArray(), 1, $frontmatterEnd - 1));
        $this->extractFrontmatter($rawFrontmatter);
        $content = implode($lines->getEol(), array_slice($lines->toArray(), $frontmatterEnd + 1));
        return $content;
    }

    /**
     * __toString returns a YAML dump of all data.
     *
     * @return string
     */
    public function __toString()
    {
        return Yaml::dump($this->data);
    }

    /**
     * extractFrontmatter into this object from a frontmatter string.
     *
     * @param string $rawFrontmatter
     */
    private function extractFrontmatter($rawFrontmatter)
    {
        $this->data = Yaml::parse($rawFrontmatter);
        if ($this->data === null) {
            $this->data = [];
        }
        if (!is_array($this->data)) {
            $this->data = [$this->data];
        }
    }

    /**
     * @param Multiline|array $lines It is assumed that the first line is known to be a valid separator.
     *
     * @return int Position of the second separator.
     */
    private function findFrontmatterEndPosition($lines)
    {
        foreach ($lines as $key => $line) {
            if ($key > 0 && $line === self::FRONTMATTER_SEPARATOR) {
                return $key;
            }
        }
        // If there was no second separator, assume it's all frontmatter and no content
        return count($lines);
    }
}
