<?php

declare(strict_types=1);

namespace Utils\Rector\Rector\ReadonlyClassWithoutGetter;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ReadonlyClassWithoutGetterRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Delete getters for readonly class with public parameters.', []);
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $methods = $node->getMethods();
        $params = null;
        foreach ($methods as $method) {
            if ($method->name->name === MethodName::CONSTRUCT) {
                $params = $method->getParams();
            } elseif (!empty($params)) {
                $methodName = $method->name->name;
                if (str_starts_with($methodName, 'get')) {
                    $methodName = lcfirst(substr($method->name->name, 3));
                }
                foreach ($params as $param) {
                    if (
                        ($param->var->name === $method->name->name || $param->var->name === $methodName)
                        && $param->type->name === $method->returnType->name
                    ) {
                        $methodStmtKey = $method->getAttribute(AttributeKey::STMT_KEY);
                        unset($node->stmts[$methodStmtKey]);
                    }
                }
            }
        }

        return $node;
    }

    private function shouldSkip(Class_ $class): bool
    {
        if ($this->shouldSkipNamespace($class->namespacedName)) {
            return true;
        }

        if ($this->shouldSkipConstruct($class)) {
            return true;
        }

        return false;
    }

    private function shouldSkipNamespace(Name $namespaceName): bool
    {
        if (\count($namespaceName->getParts()) < 6) {
            return true;
        }

        return $namespaceName->getParts()[4] !== 'ApiPlatform' && $namespaceName->getParts()[5] !== 'Resource';
    }

    private function shouldSkipConstruct(Class_ $class): bool
    {
        $properties = $class->getProperties();

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            // no __construct means no property, skip if class has no property defined
            return $properties === [];
        }

        if ($this->shouldSkipPrivateProperties($properties)) {
            return true;
        }

        $params = $constructClassMethod->getParams();
        if ($params === []) {
            // no params means no property, skip if class has no property defined
            return $properties === [];
        }
        foreach ($params as $param) {
            if ($param->flags === 4) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Property> $properties
     */
    private function shouldSkipPrivateProperties(array $properties): bool
    {
        foreach ($properties as $property) {
            if ($property->isPublic() === false) {
                return true;
            }
        }

        return false;
    }
}
