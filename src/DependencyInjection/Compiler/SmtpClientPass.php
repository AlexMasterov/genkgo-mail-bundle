<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\{
    ChildDefinition,
    Compiler\CompilerPassInterface,
    ContainerBuilder,
    Definition,
    Reference
};

class SmtpClientPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('genkgo_mail.default_mailer')) {
            return;
        }

        $mailers = $container->findTaggedServiceIds('genkgo_mail.smtp_client', false);

        foreach ($mailers as $mailer => [$options]) {
            $this->configureClient($mailer, $options, $container);
        }
    }

    /**
     * @param string $mailer
     * @param array $options
     * @param ContainerBuilder $container
     */
    private function configureClient(string $mailer, array $options, ContainerBuilder $container): void
    {
        $factoryId = "{$mailer}.protocol.client_factory";
        $container->setDefinition($factoryId, $this->factoryDefinition($options));

        $clientId = "{$mailer}.protocol.client";
        $container->setDefinition($clientId, $this->clientDefinition($factoryId));

        $container->getDefinition($mailer)->replaceArgument(0, new Reference($clientId));
    }

    /**
     * @param string $factoryId
     * @return Definition
     */
    private function clientDefinition(string $factoryId): Definition
    {
        return (new Definition)
            ->setPublic(false)
            ->setFactory([new Reference($factoryId), 'newClient']);
    }

    /**
     * @param array $options
     * @return ChildDefinition
     */
    private function factoryDefinition(array $options): ChildDefinition
    {
        $definition = new ChildDefinition('genkgo_mail.protocol.smtp.client_factory.abstract');
        $definition->replaceArgument(0, $this->dsn($options));

        if (isset($options['auth_mode'], $options['username'], $options['password'])) {
            $definition->addMethodCall('withAuthentication', [
                \array_flip(['none', 'plain', 'login', 'auto'])[$options['auth_mode']],
                $options['username'],
                $options['password'],
            ]);
        }

        return $definition;
    }

    /**
     * @param array $options
     * @return string
     */
    private function dsn(array $options): string
    {
        ['host' => $host, 'port' => $port] = $options;

        static $connection = [
            'ssl' => 'smtps',
            'tls' => 'smtp-starttls',
        ];

        $schema = $connection[$options['encryption']] ?? 'smtp';
        $query = \http_build_query([
            'ehlo'           => $options['local_domain'],
            'timeout'        => $options['timeout'],
            'reconnectAfter' => $options['reconnect_after'],
            'crypto'         => $options['crypto'],
        ]);

        return "{$schema}://{$host}:{$port}/?{$query}";
    }
}
