<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection;

trait ConfigurationStub
{
    protected function normalizeRoot(array $config = []): array
    {
        $default = [
            'default_transport' => 'default',
            'transports' => [
                'default' => [
                    'smtp' => $this->normalizeSmtpNode(),
                ],
            ],
        ];

        return array_replace_recursive($default, $config);
    }

    protected function normalizeSmtpNode(array $config = []): array
    {
        static $default = [
            'lazy'            => false,
            'host'            => 'localhost',
            'port'            => null,
            'username'        => null,
            'password'        => null,
            'timeout'         => 30,
            'reconnect_after' => null,
            'local_domain'    => null,
            'auth_mode'       => 'auto',
            'encryption'      => null,
            'crypto'          => null,
        ];

        return array_replace($default, $config);
    }
}
