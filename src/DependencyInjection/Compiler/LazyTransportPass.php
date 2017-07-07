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
        if (false === $container->hasParameter('genkgo_mail.default_mailer')) {
            return;
        }

        $mailers = $container->findTaggedServiceIds('genkgo_mail.transport.lazy', false);

        foreach (\array_keys($mailers) as $mailer) {
            $this->configureTransport($mailer, $container);
        }
    }

    /**
     * @param string $mailer
     * @param ContainerBuilder $container
     */
    private function configureTransport(string $mailer, ContainerBuilder $container): void
    {
        $transportId = "{$mailer}.transport.lazy";

        $definition = (new ChildDefinition('genkgo_mail.transport.lazy.abstract'))
            ->setDecoratedService($mailer, $transportId)
            ->addArgument(new ServiceClosureArgument(new Reference($transportId)));

        $container->setDefinition("{$mailer}.lazy", $definition);
    }
}
