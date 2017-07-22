<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection\Compiler;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\Compiler\LazyTransportPass;
use AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection\Compiler\TestTransport;
use AlexMasterov\GenkgoMailBundle\Transport\LazyTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LazyTransportPassTest extends TestCase
{
    /** @test */
    public function it_valid_processed_then_no_transport()
    {
        $container = $this->container();
        $container->getParameterBag()->clear();

        // Execute
        $container->compile();

        // Verify
        self::assertFalse($this->hasLazyTransport($container));

        $container = $this->container();
        $container->setParameter('genkgo_mail.transport', true);

        // Execute
        $container->compile();

        // Verify
        self::assertFalse($this->hasLazyTransport($container));
    }

    /** @test */
    public function it_valid_processed_when_tagged()
    {
        $container = $this->container();
        $container->register('test_transport.lazy', TestTransport::class)
            ->addTag('genkgo_mail.transport.lazy');

        // Execute
        $container->compile();

        // Verify
        self::assertTrue($this->hasLazyTransport($container));
        self::assertInstanceOf(
            LazyTransport::class,
            $container->get('test_transport.lazy')
        );
    }

    private function hasLazyTransport(ContainerBuilder $container): bool
    {
        $hasLazyClass = array_filter(
            $container->getDefinitions(),
            function ($definition) {
                return $definition->getClass() === LazyTransport::class;
            }
        );

        return (bool) $hasLazyClass;
    }

    private function container(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->addCompilerPass(new LazyTransportPass());
        $container->setParameter('genkgo_mail.transport', true);
        $container->register('genkgo_mail.transport.lazy.abstract', LazyTransport::class)
            ->setAbstract(true);

        return $container;
    }
}
