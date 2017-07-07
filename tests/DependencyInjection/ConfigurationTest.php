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
            'transport' => 'smtp',
        ];

        $normalized = $this->normalizeRoot();

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_default_mailer()
    {
        $config = [
            'default_mailer' => 'mock_default',
            'transport' => 'smtp',
        ];

        $normalized = [
            'default_mailer' => $config['default_mailer'],
            'mailers' => [
                $config['default_mailer'] => $this->normalizeNodeTransport(),
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_non_existent_default_mailer()
    {
        $config = [
            'default_mailer' => 'invalid',
            'mailers' => [
                'valid' => $this->nodeTransport(),
            ],
        ];

        $normalized = [
            'default_mailer' => 'valid',
            'mailers' => [
                'valid' => $this->normalizeNodeTransport(),
            ],
        ];

        self::assertProcessedConfigurationEquals($normalized, $config);
    }

    /**
     * @test
     */
    public function is_valid_processed_crypto_method()
    {
        $config = $this->nodeTransport([
            'crypto' => ['SSLv2_CLIENT'],
        ]);

        $normalized = $this->normalizeRoot([
            'mailers' => [
                'default' => [
                    'crypto' => 3,
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
            'crypto' => ['INVALID_CRYPTO'],
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
