<?php
namespace nochso\WriteMe\CLI;

use Aura\Cli\Stdio\Formatter;
use Aura\Cli\Stdio\Handle;
use nochso\Omni\Multiline;

class Stdio extends \Aura\Cli\Stdio
{
    /**
     * @param string $stdin
     * @param string $stout
     * @param string $stderr
     *
     * @return \nochso\WriteMe\CLI\Stdio
     */
    public static function create($stdin = 'php://stdin', $stout = 'php://stdout', $stderr = 'php://stderr')
    {
        return new self(
            new Handle($stdin, 'r'),
            new Handle($stout, 'w+'),
            new Handle($stderr, 'w+'),
            new Formatter()
        );
    }

    /**
     * Prints formatted text to standard error **without** a trailing newline.
     *
     * @param string $string The text to print to standard error.
     *
     * @return null
     */
    public function err($string = null)
    {
        $template = '<<red>>%s<<reset>>';
        $string = sprintf($template, $string);
        $string = $this->formatter->format($string, $this->stderr->isPosix());
        $this->stderr->fwrite($string);
    }

    /**
     * Prints formatted text to standard error **with** a trailing newline.
     *
     * @param string $string The text to print to standard error.
     *
     * @return null
     */
    public function errln($string = null)
    {
        $this->errln($string . PHP_EOL);
    }

    /**
     * @param \Throwable $throwable
     */
    public function exception($throwable)
    {
        if (!$throwable instanceof \Exception && !$throwable instanceof \Throwable) {
            $this->errln($throwable);
            return;
        }
        $template = <<<TAG
<<bold red>>%s<<reset>>
<<red>>%s<<reset>>


TAG;
        $message = $throwable;
        $message = Multiline::create($message)->prefix('    ');
        $string = sprintf($template, get_class($throwable), $message);
        $string = $this->formatter->format($string, $this->stderr->isPosix());
        $this->stderr->fwrite($string);
    }
}
