<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Document;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\ParserFactory;

/**
 * Call represents a call to Placeholder methods.
 * 
 * ```
 *
 * @toc.sub 1@
 * ```
 * Where `toc` is the identifier, `sub` the method and parameters an array `[1]`
 */
class Call
{
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
     * extractFirstCall to a Placeholder method from a Document.
     *
     * @param \nochso\WriteMe\Document $document
     *
     * @return \nochso\WriteMe\Placeholder\Call|null
     */
    public static function extractFirstCall(Document $document)
    {
        $pattern = '/@([a-z]+)((?:\.)([a-z\.]+))?( (.*))?@/m';
        $call = null;
        if (preg_match($pattern, $document->getContent(), $matches) === 1) {
            $call = new self();
            $call->identifier = $matches[1];
            $call->rawCall = $matches[0];
            if (isset($matches[3])) {
                $call->method = $matches[3];
            }
            if (isset($matches[5])) {
                $call->extractParameters($matches[5]);
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