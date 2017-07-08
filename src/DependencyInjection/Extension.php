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

        ['default_mailer' => $defaultMailer, 'mailers' => $mailers] = $mergedConfig;

        $this->registerDefaultMailer($defaultMailer, $container);
        $this->registerMailers($mailers, $container);
    }

    /**
     * @param string $mailer
     * @param ContainerBuilder $container
     */
    private function registerDefaultMailer(string $mailer, ContainerBuilder $container): void
    {
        $defaultMailerId = "genkgo_mail.mailer.{$mailer}";

        $container->setAlias('genkgo_mail.mailer', $defaultMailerId);
        $container->setAlias('genkgo_mail.transport', $defaultMailerId);
        $container->setParameter('genkgo_mail.default_mailer', $defaultMailerId);
    }

    /**
     * @param array $mailers
     * @param ContainerBuilder $container
     */
    private function registerMailers(array $mailers, ContainerBuilder $container): void
    {
        foreach ($mailers as $mailer => $options) {
            ['transport' => $transport, 'lazy' => $lazy] = $options;

            $definition = new ChildDefinition("genkgo_mail.transport.{$transport}.abstract");

            if ('smtp' === $transport) {
                $definition->addTag('genkgo_mail.smtp_client', $options);
            }
            if ($lazy) {
                $definition->addTag('genkgo_mail.transport.lazy');
            }

            $container->setDefinition("genkgo_mail.mailer.{$mailer}", $definition);
        }
    }
}
