<?php
namespace nochso\WriteMe\Placeholder;

use BetterReflection\Reflection;
use nochso\WriteMe\Interfaces\Placeholder;
use nochso\WriteMe\Reflection\Method;

class MethodFactory
{
    /**
     * createFromPlaceholder extracts the methods of a placeholder that can be called from a template.
     *
     * Methods must be public and take a Call object as the first argument.
     *
     * @return \nochso\WriteMe\Reflection\Method[]
     */
    public function createFromPlaceholder(Placeholder $placeholder)
    {
        $methods = [];
        $class = Reflection\ReflectionClass::createFromInstance($placeholder);
        foreach ($class->getMethods() as $method) {
            if ($this->isCallable($method)) {
                $methods[] = new Method($placeholder, $method);
            }
        }
        return $methods;
    }

    private function isCallable(Reflection\ReflectionMethod $method)
    {
        if (!$method->isPublic()) {
            return false;
        }
        if ($method->getNumberOfRequiredParameters() < 1) {
            return false;
        }
        $firstParameter = $method->getParameters()[0];
        if ((string) $firstParameter->getTypeHint() !== '\\' . Call::class) {
            return false;
        }
        return true;
    }
}
