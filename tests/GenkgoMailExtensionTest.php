<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\{
    Compiler\SmtpClientPass,
    GenkgoMailExtension
};
use Genkgo\Mail\TransportInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GenkgoMailExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_valid_register_default_mailer()
    {
        $config = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => [
                    'transport' => 'sendmail',
                ],
            ],
        ];

        $defaultMailer = 'genkgo_mail.mailer.' . $config['default_mailer'];
        $container = new ContainerBuilder();

        // Execute
        $this->loadExtension($config, $container);
        $container->compile();

        // Verify
        self::assertSame($defaultMailer, (string) $container->getAlias('genkgo_mail.mailer'));
        self::assertSame($defaultMailer, (string) $container->getAlias('genkgo_mail.transport'));
        self::assertTrue($container->hasParameter('genkgo_mail.default_mailer'));
        self::assertSame($defaultMailer, $container->getParameter('genkgo_mail.default_mailer'));
        self::assertInstanceOf(TransportInterface::class, $container->get($defaultMailer));
    }

    /**
     * @test
     */
    public function it_valid_register_mailers()
    {
        $config = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => [
                    'transport' => 'smtp',
                    'host' => 'localhost',
                    'port' => 25,
                    'username' => 'mock_username',
                    'password' => 'mock_password',
                    'auth_mode' => 3,
                    'timeout' => 30,
                    'local_domain' => 'localhost',
                    'encryption' => 'tls',
                    'reconnect_after' => null,
                ],
                'second' => [
                    'transport' => 'smtp',
                    'host' => 'localhost',
                    'port' => 26,
                    'username' => 'mock_username',
                    'password' => 'mock_password',
                    'auth_mode' => 3,
                    'timeout' => 30,
                    'local_domain' => 'localhost',
                    'encryption' => 'tls',
                    'reconnect_after' => null,
                ],
            ],
        ];

        $container = new ContainerBuilder();

        // Execute
        $this->loadExtension($config, $container);
        $container->addCompilerPass(new SmtpClientPass)->compile();

        // Verify
        self::assertTrue($container->has('genkgo_mail.mailer.default'));
        self::assertInstanceOf(TransportInterface::class, $container->get('genkgo_mail.mailer.default'));
        self::assertTrue($container->has('genkgo_mail.mailer.second'));
        self::assertInstanceOf(TransportInterface::class, $container->get('genkgo_mail.mailer.second'));
    }

    private function loadExtension(array $config, ContainerBuilder $container)
    {
        $getLoadInternal = function () use ($config, $container) {
            return $this->loadInternal($config, $container);
        };

        return $getLoadInternal->call(new GenkgoMailExtension);
    }
}
