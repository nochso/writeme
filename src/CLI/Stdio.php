<?php
namespace nochso\WriteMe\CLI;

use Aura\Cli\Stdio\Formatter;
use Aura\Cli\Stdio\Handle;
use nochso\Omni\Multiline;
use nochso\Omni\Type;

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

    /**
     * Ask the user a question.
     *
     * @param string          $question  The question to display.
     * @param null            $default   Default value to use when nothing was entered.
     * @param string|callable $validator Validate the input. Can be a regular expression (must match) or callable (must return true if valid).
     *
     * @return mixed
     */
    public function ask($question, $default = null, $validator = null)
    {
        $prompt = $question;
        if ($default !== null) {
            $prompt .= sprintf(' [<<bold yellow>>%s<<reset>>]', $default);
        }
        $prompt .= ' ';
        $this->out($prompt);

        $input = $this->in();
        // Keep asking
        while (!$this->validate($input, $validator)) {
            // Abort early if there's a default value available and user did not enter anything.
            if ($default !== null && strlen(trim($input)) === 0) {
                $input = $default;
                break;
            }
            // Otherwise keep asking
            $this->out($prompt);
            $input = $this->in();
        }
        return $input;
    }

    /**
     * Validate user input using regular expressions or callable (must return true if valid).
     *
     * @param string               $input
     * @param string|callable|null $validator
     *
     * @return bool True if valid, false otherwise.
     */
    private function validate($input, $validator = null)
    {
        // Always valid when there's no validator.
        if ($validator === null) {
            return true;
        }
        if (is_string($validator)) {
            $regex = $validator;
            $validator = function ($input) use ($regex) {
                return preg_match($regex, $input) === 1;
            };
        }
        if (!is_callable($validator)) {
            throw new \RuntimeException(sprintf(
                "Stdio::validate must be called with a regular expression or callable. '%s' given.",
                Type::summarize($validator)
            ));
        }
        return $validator($input);
    }
}
