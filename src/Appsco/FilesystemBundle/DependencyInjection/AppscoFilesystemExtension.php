<?php

namespace Appsco\FilesystemBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AppscoFilesystemExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        if($config['rackspace']['enabled']){
            $loader->load('rackspace.yml');
            $this->processRackspaceConfiguration($config['rackspace'], $container);
        }

    }

    private function processRackspaceConfiguration(array $config, ContainerBuilder $container){
        $def = $container->getDefinition('appsco_filesystem.client')
            ->setArguments([
                    $config['client']['url'],
                    [
                        'username' => $config['client']['username'],
                        'apiKey' => $config['client']['apikey']
                    ]
                ]);

        $objectstore = array_merge(
            [$def],
            array_values($config['objectstore'])
        );
        $container->getDefinition('appsco_filesystem.objectstore')
            ->setArguments($objectstore);
    }
}
