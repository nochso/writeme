<?php
namespace nochso\WriteMe\CLI;

use Aura\Cli\Stdio\Formatter;
use Aura\Cli\Stdio\Handle;
use nochso\Omni\Multiline;
use nochso\Omni\Strings;
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
     * Gets user input from the command line and trims the end-of-line.
     *
     * @return string
     */
    public function in()
    {
        $this->out(' <<yellow>>><<reset>> ');
        return parent::in();
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
            $prompt .= sprintf(' [<<yellow>>%s<<reset>>]', $default);
        }
        $this->out($prompt);

        $input = $this->in();
        $this->outln();
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
            $this->outln();
        }
        return $input;
    }

    /**
     * Confirm a yes/no question.
     *
     * @param string $question     The question to confirm.
     * @param bool   $defaultToYes Optional, defaults to true. If true, empty input means confirmation. If false, empty
     *                             input means cancelling.
     *
     * @return bool
     */
    public function confirm($question, $defaultToYes = true)
    {
        $question = sprintf('%s (y/n)', $question);
        $defaultChar = $defaultToYes ? 'y' : 'n';
        $pattern = '/^(y|n)/i';
        $answer = $this->ask($question, $defaultChar, $pattern);
        return strtolower($answer[0]) === 'y';
    }

    /**
     * Choose an element from a list.
     *
     * @param string[] $list       List of elements to choose from.
     * @param string   $prompt     Optional question prompt to display.
     * @param bool     $required   Optional, defaults to true. If true, user must select an element.
     * @param bool     $singleLine Optional, defaults to false. If true, all elements will be shown on a single line.
     *
     * @return mixed|null The key of the selected element.
     */
    public function chooseFromList($list, $prompt = 'Choose an item', $required = true, $singleLine = false)
    {
        do {
            $this->displayList($list, $singleLine);
            $this->outln();
            $this->out($prompt);
            $in = $this->in();
            if (!$required && $in === '') {
                return null;
            }
        } while (!isset($list[$in]));
        return $in;
    }

    /**
     * displayList with keys and values.
     *
     * @param string[] $list       List of elements to display. Keys can be integers or strings.
     * @param bool     $singleLine Optional, defaults to false. If true, all elements will be shown on a single line.
     */
    public function displayList($list, $singleLine = false)
    {
        $this->outln();
        foreach ($list as $key => $value) {
            if (Strings::startsWith((string)$value, (string)$key)) {
                $this->out(sprintf('<<yellow>>%s<<reset>>%s', $key, mb_substr($value, mb_strlen($key))));
            } else {
                $this->out(sprintf('<<yellow>>%s<<reset>> %s', $key, $value));
            }
            if ($singleLine) {
                $this->out(' ');
            } else {
                $this->out(PHP_EOL);
            }
        }
    }

    /**
     * stripFormat removes all aura/cli formatting strings.
     *
     * @param string $input
     *
     * @return string The string with all formatting stripped away.
     */
    public function stripFormat($input)
    {
        $formatter = new Formatter();
        return $formatter->format($input, false);
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

    /**
     * fillKeysWithActionShortcuts returns a new string array with string keys based on the beginnings of the strings.
     *
     * This is used for displaying a selection of actions for the user to choose from.
     *
     * @param string[] $actions The string array to fill with unique keys.
     *
     * @throws \InvalidArgumentException If the array values are not unique.
     *
     * @return string[] If the input array already contains strings as keys, the original input is returned.
     */
    private function fillKeysWithActionShortcuts($actions)
    {
        // Assume there's nothing to do if keys already contain strings
        $stringKeys = array_filter(array_keys($actions), 'is_string');
        if (count($stringKeys) > 0) {
            return $actions;
        }
        if (count(array_unique(array_values($actions))) !== count($actions)) {
            throw new \InvalidArgumentException('All actions must be unique.');
        }
        // Action => key
        $flippedActions = array_flip($actions);
        // Sort array by action keys so that 'a' becomes before 'aa'.
        // This way the following loop won't assign prefix 'a' to string 'aa' first:
        // If string 'a' followed, there would be no way to use it for another unique key.
        $sortedFlippedActions = $flippedActions;
        ksort($sortedFlippedActions);

        $shortcutActionMap = [];
        foreach ($sortedFlippedActions as $action => $originalKey) {
            $shortcutLength = 1;
            // Keep adding more characters until a unique shortcut is found.
            do {
                $shortcut = mb_substr($action, 0, $shortcutLength);
                $shortcutLength++;
            } while (isset($shortcutActionMap[$shortcut]));
            $shortcutActionMap[$shortcut] = $action;
        }
        // Action => shortcut
        $actionShortcutMap = array_flip($shortcutActionMap);
        // Now make sure the new array is in the same order as the input
        $actionMap = [];
        foreach ($actions as $action) {
            $shortcut = $actionShortcutMap[$action];
            $actionMap[$shortcut] = $action;
        }
        return $actionMap;
    }
}
