<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

use AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection\ExtensionTrait;
use Genkgo\Mail\TransportInterface;
use PHPUnit\Framework\TestCase;

class GenkgoMailExtensionTest extends TestCase
{
    use ExtensionTrait;

    /**
     * @test
     */
    public function it_valid_register_default_mailer()
    {
        $config = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => [
                    'transport' => 'smtp',
                    'username'  => 'mock_username',
                    'password'  => 'mock_password',
                    'auth_mode' => 'auto',
                ],
            ],
        ];

        $defaultMailer = 'genkgo_mail.mailer.' . $config['default_mailer'];

        // Execute
        $container = $this->container($config);
        $container->compile();

        // Verify
        self::assertSame($defaultMailer, $container->getParameter('genkgo_mail.default_mailer'));
        self::assertSame($defaultMailer, (string) $container->getAlias('genkgo_mail.mailer'));
        self::assertSame($defaultMailer, (string) $container->getAlias('genkgo_mail.transport'));
        self::assertInstanceOf(TransportInterface::class, $container->get($defaultMailer));
    }

    /**
     * @test
     */
    public function it_valid_register_lazy_transport()
    {
        $config = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => [
                    'transport' => 'smtp',
                    'lazy'      => true,
                ],
            ],
        ];

        $defaultMailer = 'genkgo_mail.mailer.' . $config['default_mailer'];

        // Execute
        $container = $this->container($config);
        $container->compile();

        // Verify
        self::assertSame("{$defaultMailer}.lazy", (string) $container->getAlias('genkgo_mail.mailer'));
        self::assertInstanceOf(TransportInterface::class, $container->get($defaultMailer));

        $getTransportMethod = function () {
            return $this->getTransport();
        };

        $transportFromClosure = $getTransportMethod->call($container->get($defaultMailer));

        self::assertInstanceOf(TransportInterface::class, $transportFromClosure);
    }
}
