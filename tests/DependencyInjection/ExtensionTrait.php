<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\{
    Configuration,
    Extension
};
use AlexMasterov\GenkgoMailBundle\GenkgoMailBundle;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ExtensionTrait
{
    private function container(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder();

        // Apply compiler passes
        $bundle = new GenkgoMailBundle();
        $bundle->build($container);

        $config = $this->processConfiguration($config);

        $getLoadInternal = function () use ($config, $container) {
            return $this->loadInternal($config, $container);
        };

        $getLoadInternal->call($bundle->getContainerExtension());

        return $container;
    }

    private function processConfiguration(array $config = []): array
    {
        $configuration = new Configuration();
        $config = ['genkgo_mail' => $config];

        return (new Processor)->processConfiguration($configuration, $config);
    }
}
