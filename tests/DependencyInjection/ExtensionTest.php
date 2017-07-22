<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

use AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection\ExtensionTrait;
use Genkgo\Mail\TransportInterface;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{
    use ExtensionTrait;

    /**
     * @test
     */
    public function it_valid_register_default_transport()
    {
        $config = [
            'default_transport' => 'default',
            'transports' => [
                'default' => [
                    'smtp' => [
                        'username'  => 'mock_username',
                        'password'  => 'mock_password',
                        'auth_mode' => 'auto',
                    ],
                ],
            ],
        ];

        $defaultMailer = 'genkgo_mail.transport.' . $config['default_transport'];

        // Execute
        $container = $this->container($config);
        $container->compile();

        // Verify
        self::assertSame($defaultMailer, (string) $container->getAlias('genkgo_mail.transport'));
        self::assertInstanceOf(TransportInterface::class, $container->get($defaultMailer));
    }
}
