<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\Configuration;
use AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection\ConfigurationStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\{
    Exception\InvalidConfigurationException,
    Processor
};

final class ConfigurationTest extends TestCase
{
    use ConfigurationStub;

    /**
     * @test
     */
    public function it_valid_processed_transport()
    {
        $config = [
            'smtp' => [],
        ];

        $normalized = $this->normalizeRoot();

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_default_transport()
    {
        $config = [
            'default_transport' => 'mock_default',
            'smtp' => [],
        ];

        $normalized = [
            'default_transport' => $config['default_transport'],
            'transports' => [
                $config['default_transport'] => [
                    'smtp' => $this->normalizeSmtpNode(),
                ],
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_crypto_method()
    {
        $config = [
            'smtp' => [
                'crypto' => 'SSLv2_CLIENT',
            ],
        ];

        $normalized = $this->normalizeRoot([
            'transports' => [
                'default' => [
                    'smtp' => $this->normalizeSmtpNode([
                        'crypto' => 3,
                    ]),
                ],
            ],
        ]);

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_invalid_crypto_method()
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = [
            'smtp' => [
                'crypto' => 'INVALID_CRYPTO',
            ],
        ];

        $normalized = $this->normalizeRoot();

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    private static function assertProcessedConfigurationEquals($expected, $config): void
    {
        $actual = self::processConfiguration($config);

        self::assertEquals($expected, $actual);
    }

    private static function processConfiguration(array $config = []): array
    {
        $configuration = new Configuration();
        $rootNode = ['genkgo_mail' => $config];

        return (new Processor)->processConfiguration($configuration, $rootNode);
    }
}
