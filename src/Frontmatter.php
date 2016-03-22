<?php
namespace nochso\WriteMe;

use nochso\Omni\Dot;
use nochso\Omni\EOL;
use nochso\Omni\Strings;
use Symfony\Component\Yaml\Yaml;

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
     * @param string     $dotPath
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function get($dotPath, $default = null)
    {
        return Dot::get($this->data, $dotPath, $default);
    }

    public function set($dotPath, $value)
    {
        Dot::set($this->data, $dotPath, $value);
    }

    /**
     * @param string $input
     */
    public function parse($input)
    {
        $this->data = Yaml::parse($input);
    }

    /**
     * Extract frontmatter from a raw document and return the remaining document content.
     *
     * @param string $input
     *
     * @return string The remaining document content.
     */
    public function extract($input)
    {
        // Content only because frontmatter must start with a separator
        if (!Strings::startsWith($input, self::FRONTMATTER_SEPARATOR)) {
            return $input;
        }
        $sections = explode(self::FRONTMATTER_SEPARATOR, $input);
        $count = count($sections);
        // Still content only because there must be at least 2 separators
        if ($count < 3) {
            return $input;
        }
        $this->parse($sections[1]);
        // Implode all remaining sections so `---` can be used in the content without cutting off any content.
        $content = implode(self::FRONTMATTER_SEPARATOR, array_slice($sections, 2));
        return $this->trimFirstEOL($content);
    }

    /**
     * @param $input
     *
     * @return string
     */
    private function trimFirstEOL($input)
    {
        $eol = (string) EOL::detectDefault($input);
        if (Strings::startsWith($input, $eol)) {
            return substr($input, strlen($eol));
        }
        return $input;
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
}
