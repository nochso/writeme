<?php
namespace nochso\WriteMe\Markdown;

use nochso\Omni\Path;

/**
 * MarkdownTemplate to help write Markdown templates in pure PHP.
 */
abstract class Template
{
    const INDENT_TAB = "\t";
    const INDENT_SPACE_TWO = ' ';
    const INDENT_SPACE_FOUR = '    ';

    protected $baseFolder = __DIR__ . '/../asset';
    private $headerStartLevel = 0;
    private $indentStartLevel = 0;
    private $indentStyle = self::INDENT_SPACE_FOUR;

    /**
     * @param string $filepath
     *
     * @return string
     */
    public function render($filepath)
    {
        $path = Path::combine($this->baseFolder, $filepath);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    /**
     * @param int    $level
     * @param string $text  Optional.
     *
     * @return string
     */
    public function header($level, $text = '')
    {
        $header = str_repeat('#', $this->headerStartLevel + $level);
        $headerText = sprintf('%s %s', $header, $text);
        // Trim trailing space if $text is empty.
        return rtrim($headerText, ' ');
    }

    /**
     * @param int $indentStartLevel
     */
    public function setIndentStartLevel($indentStartLevel)
    {
        $this->indentStartLevel = $indentStartLevel;
    }

    /**
     * @param int $level
     */
    public function setHeaderStartLevel($level)
    {
        $this->headerStartLevel = $level;
    }

    /**
     * @param string $indentStyle
     */
    public function setIndentStyle($indentStyle)
    {
        $this->indentStyle = $indentStyle;
    }

    /**
     * @param int    $level
     * @param string $text
     *
     * @return string
     */
    public function indent($level, $text = '')
    {
        $indent = str_repeat($this->indentStyle, $this->indentStartLevel + $level);
        return $indent . $text;
    }
}
