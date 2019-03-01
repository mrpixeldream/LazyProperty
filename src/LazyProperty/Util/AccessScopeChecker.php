<?php

declare(strict_types=1);

namespace LazyProperty\Util;

use LazyProperty\Exception\InvalidAccessException;
use ReflectionException;
use ReflectionProperty;
use function get_class;
use function is_subclass_of;

/**
 * Utility class to identify scope access violations
 */
class AccessScopeChecker
{
    /**
     * Utility used to verify that access to lazy properties is not happening from outside allowed scopes
     *
     * @internal
     *
     * @param array $caller the caller array as from the debug stack trace entry
     *
     * @throws InvalidAccessException
     * @throws ReflectionException
     *
     * @private
     */
    public static function checkCallerScope(array $caller, object $instance, string $property) : void
    {
        $reflectionProperty = new ReflectionProperty($instance, $property);

        if (! $reflectionProperty->isPublic()) {
            if (! isset($caller['object'])) {
                throw InvalidAccessException::invalidContext(null, $instance, $property);
            }

            $caller        = $caller['object'];
            $callerClass   = get_class($caller);
            $instanceClass = get_class($instance);

            if ($callerClass === $instanceClass
                || ($reflectionProperty->isProtected() && is_subclass_of($callerClass, $instanceClass))
                || $callerClass === 'ReflectionProperty'
                || is_subclass_of($callerClass, 'ReflectionProperty')
            ) {
                return;
            }

            throw InvalidAccessException::invalidContext($caller, $instance, $property);
        }
    }
}
