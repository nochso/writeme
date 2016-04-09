<?php
namespace nochso\WriteMe\Reflection;

use BetterReflection\Reflection;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Markdown\DocBlock;
use nochso\WriteMe\Placeholder\Call;
use nochso\WriteMe\Reflection\Parameter;

/**
 * Method links a template call and the matching class method.
 */
class Method
{
    const CAMEL_CASE_SPLIT = '/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/';
    /**
     * Class method name of a wildcard: gets called when no other names match.
     */
    const WILDCARD_METHOD_NAME = 'wildcard';

    /**
     * @var \nochso\WriteMe\Interfaces\Placeholder
     */
    private $placeholder;
    /**
     * @var \BetterReflection\Reflection\ReflectionMethod
     */
    private $method;
    private $dotName;

    public function __construct(Placeholder $placeholder, Reflection\ReflectionMethod $method)
    {
        $this->placeholder = $placeholder;
        $this->method = $method;
        $parts = preg_split(self::CAMEL_CASE_SPLIT, $this->method->getShortName());
        $dotted = implode('.', $parts);
        $this->dotName = strtolower($dotted);
    }

    /**
     * getDotName of a camelCased class method.
     *
     * @return string The dotted name, e.g. fooBar => foo.bar
     */
    public function getDotName()
    {
        return $this->dotName;
    }

    /**
     * @return \nochso\WriteMe\Interfaces\Placeholder
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function hasPriorityOfCall(Call $call)
    {
        return in_array($call->getPriority(), $this->getPlaceholder()->getCallPriorities());
    }

    public function getDotSignature()
    {
        $params = [];
        $parameters = array_slice($this->method->getParameters(), 1);
        /** @var Reflection\ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            $params[] = '$' . $parameter->getName();
        }
        if (count($params) > 0) {
            $params = '(' . implode(', ', $params) . ')';
        } else {
            $params = '';
        }
        $dotName = $this->getDotName();
        if ($dotName === self::WILDCARD_METHOD_NAME) {
            $dotName = '*';
        }
        return sprintf('@%s%s@', $dotName, $params);
    }

    /**
     * @return \BetterReflection\Reflection\ReflectionMethod
     */
    public function getReflectionMethod()
    {
        return $this->method;
    }

    /**
     * @return \nochso\WriteMe\Markdown\DocBlock
     */
    public function getDocBlock()
    {
        return new DocBlock($this->method->getDocComment());
    }

    /**
     * @return \nochso\WriteMe\Reflection\Parameter[]
     */
    public function getParametersWithoutCall()
    {
        $parameters = [];
        foreach (array_slice($this->method->getParameters(), 1) as $param) {
            $parameters[] = new Parameter($param);
        }
        return $parameters;
    }

    /**
     * Call a placeholder method with the Call object and parameters extracted from the raw template call.
     */
    public function call(Call $call)
    {
        $callable = [$this->placeholder, $this->method->getShortName()];
        $params = array_merge([$call], $call->getParameters());
        call_user_func_array($callable, $params);
    }
}
