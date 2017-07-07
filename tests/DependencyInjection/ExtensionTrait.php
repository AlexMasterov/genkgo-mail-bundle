<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\{
    Configuration,
    GenkgoMailExtension
};
use AlexMasterov\GenkgoMailBundle\GenkgoMailBundle;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ExtensionTrait
{
    private function container(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $this->buildBuindle($container);
        $this->loadExtension($config, $container);

        return $container;
    }

    private function buildBuindle(ContainerBuilder $container): ContainerBuilder
    {
        $bundle = new GenkgoMailBundle();
        $bundle->build($container);

        return $container;
    }

    private function loadExtension(array $config, ContainerBuilder $container): ContainerBuilder
    {
        $config = $this->processConfiguration($config);

        $getLoadInternal = function () use ($config, $container) {
            return $this->loadInternal($config, $container);
        };

        $getLoadInternal->call(new GenkgoMailExtension);

        return $container;
    }

    private function processConfiguration(array $config = []): array
    {
        $configuration = new Configuration();
        $config = ['genkgo_mail' => $config];

        return (new Processor)->processConfiguration($configuration, $config);
    }
}
