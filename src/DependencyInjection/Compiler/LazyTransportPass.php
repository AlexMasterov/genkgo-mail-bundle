<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\DependencyInjection\Compiler;

use AlexMasterov\GenkgoMailBundle\Transport\LazyTransport;
use Symfony\Component\DependencyInjection\{
    Argument\ServiceClosureArgument,
    ChildDefinition,
    Compiler\CompilerPassInterface,
    ContainerBuilder,
    Reference
};

class LazyTransportPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('genkgo_mail.transport')) {
            return;
        }

        $services = $container->findTaggedServiceIds('genkgo_mail.transport.lazy', true);
        if (empty($services)) {
            return;
        }

        foreach (\array_keys($services) as $transport) {
            $this->configureTransport($transport, $container);
        }
    }

    /**
     * @param string $transport
     * @param ContainerBuilder $container
     */
    private function configureTransport(string $transport, ContainerBuilder $container): void
    {
        $decoratedTransport = "{$transport}.transport.lazy";

        $definition = (new ChildDefinition('genkgo_mail.transport.lazy.abstract'))
            ->setDecoratedService($transport, $decoratedTransport)
            ->addArgument(new ServiceClosureArgument(new Reference($decoratedTransport)));

        $container->setDefinition("{$transport}.lazy", $definition);
    }
}
