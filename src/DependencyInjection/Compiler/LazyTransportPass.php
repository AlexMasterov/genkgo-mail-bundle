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
        if (false === $container->getParameter('genkgo_mail.transport')) {
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
        $decoratedId = "{$transport}.transport.lazy";

        $definition = (new ChildDefinition('genkgo_mail.transport.lazy.abstract'))
            ->setDecoratedService($transport, $decoratedId)
            ->addArgument(new ServiceClosureArgument(new Reference($decoratedId)));

        $container->setDefinition("{$transport}.lazy", $definition);
    }
}
