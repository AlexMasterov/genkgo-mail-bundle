<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\{
    Exception\InvalidConfigurationException,
    Processor
};

class ConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function it_valid_processed_blank_config()
    {
        $config = [
            'transport' => 'smtp',
        ];

        $normalized = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => [
                    'transport' => 'smtp',
                    'host' => 'localhost',
                    'port' => null,
                    'username' => null,
                    'password' => null,
                    'auth_mode' => 'auto',
                    'timeout' => 30,
                    'local_domain' => null,
                    'encryption' => null,
                    'reconnect_after' => null,
                    'retry' => null,
                ],
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_default_mailer()
    {
        $config = $this->config([
            'default_mailer' => 'mock_default',
        ]);

        $normalized = [
            'default_mailer' => $config['default_mailer'],
            'mailers' => [
                $config['default_mailer'] => $this->config([
                    'auth_mode' => 3,
                    'crypto' => 3,
                ]),
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_if_default_mailer_is_invalid()
    {
        $config = [
            'default_mailer' => 'invalid_mailer',
            'mailers' => [
                'default' => $this->config(),
            ],
        ];

        $normalized = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => $this->config([
                    'auth_mode' => 3,
                    'crypto' => 3,
                ]),
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_crypto()
    {
        $config = $this->config([
            'default_mailer' => 'mock_default',
        ]);

        $normalized = [
            'default_mailer' => $config['default_mailer'],
            'mailers' => [
                $config['default_mailer'] => $this->config([
                    'auth_mode' => 3,
                    'crypto' => 3,
                ]),
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_then_crypto_is_invalid()
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = $this->config([
            'default_mailer' => 'mock_default',
            'crypto' => ['INVALID_CRYPTO'],
        ]);

        $normalized = [
            'default_mailer' => $config['default_mailer'],
            'mailers' => [
                $config['default_mailer'] => $this->config([
                    'auth_mode' => 3,
                    'crypto' => 3,
                ]),
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    private static function processConfiguration(array $config = []): array
    {
        $configuration = new Configuration();
        $config = ['genkgo_mail' => $config];

        return (new Processor)->processConfiguration($configuration, $config);
    }

    private static function assertProcessedConfigurationEquals($expected, $config): void
    {
        $actual = self::processConfiguration($config);

        self::assertEquals($expected, $actual);
    }

    private function config(array $merge = []): array
    {
        static $default = [
            'transport' => 'smtp',
            'host' => 'mock_host',
            'port' => 25,
            'username' => 'mock_username',
            'password' => 'mock_password',
            'auth_mode' => 'auto',
            'timeout' => 30,
            'encryption' => null,
            'crypto' => ['SSLv2_CLIENT'],
            'local_domain' => null,
            'encryption' => null,
            'reconnect_after' => null,
            'retry' => null,
        ];

        return array_replace($default, $merge);
    }
}
