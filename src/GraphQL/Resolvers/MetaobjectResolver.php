<?php

namespace Shopper\GraphQL\Resolvers;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Shopper\Models\Metaobject;
use Shopper\Models\MetaobjectDefinition;

class MetaobjectResolver
{
    public function createDefinition($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): MetaobjectDefinition
    {
        return MetaobjectDefinition::create($args['input'] ?? $args);
    }

    public function updateDefinition($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): MetaobjectDefinition
    {
        $definition = MetaobjectDefinition::findOrFail($args['id']);
        $definition->update($args['input'] ?? []);

        return $definition->fresh();
    }

    public function deleteDefinition($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $definition = MetaobjectDefinition::findOrFail($args['id']);

        // Delete all associated metaobjects
        $definition->metaobjects()->delete();

        return $definition->delete();
    }

    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Metaobject
    {
        return Metaobject::create($args['input'] ?? $args);
    }

    public function update($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Metaobject
    {
        $metaobject = Metaobject::findOrFail($args['id']);
        $metaobject->update($args['input'] ?? []);

        return $metaobject->fresh(['definition']);
    }

    public function delete($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $metaobject = Metaobject::findOrFail($args['id']);

        return $metaobject->delete();
    }

    public function publish($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Metaobject
    {
        $metaobject = Metaobject::findOrFail($args['id']);
        $metaobject->publish();

        return $metaobject->fresh(['definition']);
    }

    public function unpublish($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Metaobject
    {
        $metaobject = Metaobject::findOrFail($args['id']);
        $metaobject->unpublish();

        return $metaobject->fresh(['definition']);
    }

    public function isPublished($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        return $rootValue->isPublished();
    }

    public function displayName($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): string
    {
        return $rootValue->getDisplayName();
    }
}
