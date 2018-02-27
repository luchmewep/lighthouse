<?php

namespace Nuwave\Lighthouse\Schema\Directives\Fields;

use Closure;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;

class CanDirective implements FieldMiddleware
{
    /**
     * Resolve the field directive.
     *
     * @param FieldDefinitionNode $field
     * @param Closure             $resolver
     *
     * @return Closure
     */
    public function handle(FieldDefinitionNode $field, Closure $resolver)
    {
        // ...
    }
}