<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Document;
use nochso\WriteMe\Interfaces\Placeholder;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\ParserFactory;

/**
 * Call represents a specific call to a placeholder or one of its methods.
 *
 * ```
 * @toc.sub 1@
 * ```
 * Where `toc` is the identifier, `sub` the method and parameters an array `[1]`
 */
class Call
{
    const REGEX = '
        /(?:(?<!@)(@@)?)    # Do not allow @ prefix unless it is escaped
        (@([a-z-]+)         # @abc
        ((?:\.)([a-z\.]+))? # nothing or .foo or .foo.foo
        (\((.*?)\))?@)      # optional parameters with closing @
        /mx';

    const REGEX_ESCAPED = '
        /(@@([a-z-]+)       # @@abc
        ((?:\.)([a-z\.]+))? # nothing or .foo or .foo.foo
        (\((.*?)\))?@@)     # optional parameters with closing @@
        /mx';

    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string|null
     */
    private $method;
    /**
     * @var mixed[]|array
     */
    private $parameters = [];
    /**
     * @var string
     */
    private $rawCall;
    /**
     * @var \nochso\WriteMe\Placeholder\Document
     */
    private $document;
    /**
     * @var bool
     */
    private $isReplaced = false;
    /**
     * @var int
     */
    private $rawCallFirstPosition;
    /**
     * @var int
     */
    private $priority;

    /**
     * extractFirstCall to a Placeholder method from a Document.
     *
     * @param \nochso\WriteMe\Document $document
     * @param int                      $priority
     * @param int                      $offset
     *
     * @return Call|null
     */
    public static function extractFirstCall(Document $document, $priority = Placeholder::PRIORITY_FIRST, $offset = 0)
    {
        $call = null;
        $content = $document->getContent();
        if ($offset > 0) {
            $content = mb_substr($content, $offset);
        }
        if (preg_match(self::REGEX, $content, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $call = new self();
            $call->document = $document;
            $call->priority = $priority;
            $call->identifier = $matches[3][0];
            $call->rawCall = $matches[2][0];
            // Position of the match including additional offset from before
            $call->rawCallFirstPosition = $matches[2][1] + $offset;
            if (isset($matches[5]) && $matches[5][0] !== '') {
                $call->method = $matches[5][0];
            }
            if (isset($matches[7])) {
                $call->extractParameters($matches[7][0]);
            }
        }
        return $call;
    }

    /**
     * @return string The identifier of the placeholder.
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string|null The method to call on the placeholder. Null if no method is specified.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return \mixed[]|array List of parameter values. Can consist of (an array of) any PHP scalar value.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string The string that caused this call, e.g. `@foo.bar@`
     */
    public function getRawCall()
    {
        return $this->rawCall;
    }

    /**
     * @return \nochso\WriteMe\Document The document that the call was found in.
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Replace the call in a document with a replacement string.
     *
     * @param string $replacement The replacement string for the call placeholder.
     */
    public function replace($replacement)
    {
        if ($this->isReplaced()) {
            throw new \LogicException(sprintf("The placeholder call '%s' has already been replaced.", $this->getRawCall()));
        }
        $replacementPattern = '${1}' . addcslashes($replacement, '\\$');

        // Split content before the placeholder start
        $startContent = mb_substr($this->document->getContent(), 0, $this->rawCallFirstPosition);
        $endContent = mb_substr($this->document->getContent(), $this->rawCallFirstPosition);
        // Replace only that specific placeholder and nothing before it
        $endContent = preg_replace(self::REGEX, $replacementPattern, $endContent, 1);
        $this->document->setContent($startContent . $endContent);
        $this->isReplaced = true;
    }

    /**
     * @return int Start position of the raw call string in the document.
     */
    public function getStartPositionOfRawCall()
    {
        return $this->rawCallFirstPosition;
    }

    /**
     * @return int End position of the raw call string in the document.
     */
    public function getEndPositionOfRawCall()
    {
        return $this->getStartPositionOfRawCall() + mb_strlen($this->getRawCall());
    }

    /**
     * @return bool True if this call has been replaced by a placeholder.
     */
    public function isReplaced()
    {
        return $this->isReplaced;
    }

    /**
     * @return int The priority at the time the call was extracted during conversion.
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $rawParameters
     */
    private function extractParameters($rawParameters)
    {
        // Kind of dirty, but way cleaner than regex magic.
        $php = '<?php dummyMethod(' . $rawParameters . ');';
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        try {
            $stmts = $parser->parse($php);
            /** @var \PhpParser\Node\Expr\FuncCall $funcCall */
            $funcCall = $stmts[0];
            foreach ($funcCall->args as $arg) {
                $this->parameters[] = $this->getPhpValueFromArg($arg->value);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf(
                "Unable to parse arguments to Placeholder Call '%s': %s",
                $this->identifier . '.' . $this->method,
                $e->getMessage()
            ));
        }
    }

    /**
     * @param \PhpParser\Node $arg
     *
     * @return mixed
     */
    private function getPhpValueFromArg(Node $arg)
    {
        // Put together a simple array from Array_->value->items
        if ($arg instanceof Array_) {
            $value = [];
            foreach ($arg->items as $item) {
                // Each item of an array could either be another array or scalar value. Recursion time!
                if ($item->key === null) {
                    // There might not be a key. Append it to the array.
                    $value[] = $this->getPhpValueFromArg($item->value);
                } else {
                    $value[$item->key->value] = $this->getPhpValueFromArg($item->value);
                }
            }
            return $value;
        }
        if ($arg instanceof Node\Expr\ConstFetch) {
            $constString = $arg->name->toString();
            if ($constString === 'true') {
                return true;
            }
            if ($constString === 'false') {
                return false;
            }
            throw new \InvalidArgumentException('Unsupported PHP constant: ' . $constString);
        }
        // Otherwise it should be a scalar value
        return $arg->value;
    }
}
