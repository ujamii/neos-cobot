<?php

namespace Ujamii\Cobot\Eel;

use Neos\Eel\ProtectedContextAwareInterface;

class EnumHelper implements ProtectedContextAwareInterface
{
    /**
     * Returns all cases of the given enum class
     *
     * @param class-string $className The class name of the
     * @return array<string> The cases of the enum
     */
    public static function cases(string $className): array
    {
        if (false === class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist', $className));
        }

        if (false === method_exists($className, 'cases')) {
            throw new \InvalidArgumentException(sprintf('Class %s does not have a cases method', $className));
        }

        return $className::cases();
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
