<?php

namespace Netgen\Bundle\RichTextDataTypeBundle;

use Netgen\Bundle\RichTextDataTypeBundle\DependencyInjection\Compiler\PublicServicesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenRichTextDataTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PublicServicesPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
