<?php

declare(strict_types=1);

namespace Utils\Rector\Rector\ChangeApiPlatformResourceGetterToProperty;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MissingMethodFromReflectionException;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChangeApiPlatformResourceGetterToPropertyRector extends AbstractRector
{
    public function __construct(private readonly ReflectionResolver $reflectionResolver)
    {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use properties instead getters for ApiPlatform resource classes', []);
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $newName = $node->name->name;
        if (str_starts_with($node->name->name, 'get')) {
            $newName = lcfirst(substr($node->name->name, 3));
        }

        return $this->nodeFactory->createPropertyFetch($node->var, $newName);
    }

    private function shouldSkip(MethodCall $node): bool
    {
        if ($this->shouldSkipNamespace($node)) {
            return true;
        }

        if ($this->shouldSkipConstruct($node)) {
            return true;
        }

        return false;
    }

    private function shouldSkipNamespace(MethodCall $methodCall): bool
    {
        if (
            ($methodCall->getAttribute('scope') === null)
            || (is_string($methodCall->getAttribute('scope')->getNamespace()) === false)
        ) {
            return true;
        }

        $namespace = explode('\\', $methodCall->getAttribute('scope')->getNamespace());

        if (\count($namespace) < 6) {
            return true;
        }

        return $namespace[4] !== 'ApiPlatform' && $namespace[5] !== 'Resource';
    }

    private function shouldSkipConstruct(MethodCall $methodCall): bool
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (!$methodReflection instanceof MethodReflection) {
            return false;
        }

        $declaringClass = $methodReflection->getDeclaringClass();

        try {
            $declaringClass->getMethod($methodCall->name->name, $methodCall->getAttribute('scope'));
        } catch (MissingMethodFromReflectionException) {
            return false;
        }

        return true;
    }
}
