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
        $transports = $container->findTaggedServiceIds('genkgo_mail.transport.lazy', false);

        foreach (\array_keys($transports) as $transport) {
            $this->configureTransport($transport, $container);
        }
    }

    /**
     * @param string $transport
     * @param ContainerBuilder $container
     */
    private function configureTransport(string $transport, ContainerBuilder $container): void
    {
        $transportId = "{$transport}.transport.lazy";

        $definition = (new ChildDefinition('genkgo_mail.transport.lazy.abstract'))
            ->setDecoratedService($transport, $transportId)
            ->addArgument(new ServiceClosureArgument(new Reference($transportId)));

        $container->setDefinition("{$transport}.lazy", $definition);
    }
}
