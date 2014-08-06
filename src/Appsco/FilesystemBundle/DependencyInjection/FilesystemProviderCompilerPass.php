<?php
namespace Appsco\FilesystemBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilesystemProviderCompilerPass implements CompilerPassInterface{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if(!$container->hasDefinition('appsco_filesystem.filesystem')){
            return;
        }

        $definition = $container->getDefinition('appsco_filesystem.filesystem');

        $taggedServices = $container->findTaggedServiceIds('appsco_filesystem.adapter');

        foreach($taggedServices as $id => $tagAttributes){
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'registerDrive',
                    [new Reference($id), $attributes['alias']]
                );
            }

        }
    }
} 