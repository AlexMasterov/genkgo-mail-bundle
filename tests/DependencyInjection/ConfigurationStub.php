<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

trait ConfigurationStub
{
    protected function nodeTransport(array $config = []): array
    {
        $default = $this->normalizeNodeTransport();

        return array_replace($default, $config);
    }

    protected function normalizeNodeTransport(array $config = []): array
    {
        static $default = [
            'transport'       => 'smtp',
            'host'            => 'localhost',
            'port'            => null,
            'username'        => null,
            'password'        => null,
            'auth_mode'       => 'auto',
            'timeout'         => 30,
            'encryption'      => null,
            'crypto'          => null,
            'local_domain'    => null,
            'encryption'      => null,
            'reconnect_after' => null,
            'retry'           => null,
            'lazy'            => false,
        ];

        return array_replace($default, $config);
    }

    protected function normalizeRoot(array $config = []): array
    {
        $default = [
            'default_mailer' => 'default',
            'mailers' => [
                'default' => $this->normalizeNodeTransport(),
            ],
        ];

        return array_replace_recursive($default, $config);
    }
}
