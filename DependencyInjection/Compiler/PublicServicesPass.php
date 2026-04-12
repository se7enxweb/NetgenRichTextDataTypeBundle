<?php

namespace Netgen\Bundle\RichTextDataTypeBundle\DependencyInjection\Compiler;

use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\RichTextStorage;
use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\Type;
use eZ\Publish\SPI\Persistence\Content\Handler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PublicServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('ezpublish.spi.persistence.cache.contentHandler')) {
            $container->findDefinition('ezpublish.spi.persistence.cache.contentHandler')
                ->setPublic(true);
        }

        if ($container->has(Type::class)) {
            $container->findDefinition(Type::class)
                ->setPublic(true);
        }

        if ($container->has(RichTextStorage::class)) {
            $container->findDefinition(RichTextStorage::class)
                ->setPublic(true);
        }
    }
}
