<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\{
    ChildDefinition,
    ContainerBuilder,
    Loader\YamlFileLoader
};
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class Extension extends ConfigurableExtension
{
    /**
     * @inheritDoc
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->registerDefaultTransport($mergedConfig['default_transport'], $container);

        foreach ($mergedConfig['transports'] as $transport => $config) {
            $this->registerTransport($transport, $config, $container);
        }
    }

    /**
     * @param string $transport
     * @param ContainerBuilder $container
     */
    private function registerDefaultTransport(string $transport, ContainerBuilder $container): void
    {
        $defaultTransportId = "genkgo_mail.transport.{$transport}";

        $container->setAlias('genkgo_mail.transport', $defaultTransportId);
        $container->setParameter('genkgo_mail.default_transport', $defaultTransportId);
    }

    /**
     * @param string $transport
     * @param array $config
     */
    private function registerTransport(string $transport, array $config, ContainerBuilder $container): void
    {
        foreach ($config as $name => $options) {
            $definition = new ChildDefinition("genkgo_mail.transport.{$name}.abstract");

            if ('smtp' === $name) {
                $definition->addTag('genkgo_mail.smtp_client', $options);
            }
            if ('sendmail' === $name) {
                $definition->setArgument(1, $options['parameters']);
            }
            if ($options['lazy']) {
                $definition->addTag('genkgo_mail.transport.lazy');
            }

            $container->setDefinition("genkgo_mail.transport.{$transport}", $definition);
        }
    }
}
