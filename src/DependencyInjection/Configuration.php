<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\{
    Builder\ArrayNodeDefinition,
    Builder\TreeBuilder,
    ConfigurationInterface,
    Exception\InvalidConfigurationException
};

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('genkgo_mail');

        $this->normalizeRootNode($rootNode);
        $rootNode
            ->validate()
                ->ifTrue(static function ($value) {
                    return !isset($value['transports'][$value['default_transport']]);
                })
                ->thenInvalid('"default_transport" not found.')
            ->end()
            ->children()
                ->scalarNode('default_transport')->end()
                ->append($this->transportsNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function normalizeRootNode(ArrayNodeDefinition $rootNode)
    {
        $normalizer = static function ($value) {
            $defaultTransport = $value['default_transport'] ?? 'default';
            unset($value['default_transport']);

            $transports = $value['transports'] ?? [$defaultTransport => $value];
            return [
                'default_transport' => $defaultTransport,
                'transports' => $transports,
            ];
        };

        $rootNode->beforeNormalization()->always()->then($normalizer)->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function transportsNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('transports');
        $node
            ->ignoreExtraKeys(false)
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->append($this->smtpNode())
                    ->append($this->sendmailNode())
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function smtpNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('smtp');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('lazy')->defaultFalse()->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultNull()->end()
                ->scalarNode('username')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('timeout')->defaultValue(30)->end()
                ->scalarNode('reconnect_after')->defaultNull()->example('PT1S')->end()
                ->scalarNode('local_domain')->defaultNull()->example('localhost')->end()
                ->enumNode('auth_mode')->defaultValue('auto')
                    ->values(['none', 'plain', 'login', 'auto'])
                ->end()
                ->enumNode('encryption')->defaultNull()
                    ->values(['tls', 'ssl', null])
                ->end()
                ->scalarNode('crypto')->defaultNull()->example('TLSv1_0_CLIENT, TLSv1_1_CLIENT')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            $methods = \preg_split('/\s*,\s*/', $v, -1, \PREG_SPLIT_NO_EMPTY);

                            static $crypto = null;
                            static $missingMethods = [];
                            foreach ($methods as $method) {
                                $constant = "STREAM_CRYPTO_METHOD_{$method}";
                                if (\defined($constant)) {
                                    $crypto |= \constant($constant);
                                } else {
                                    $missingMethods[] = $constant;
                                }
                            }

                            if (empty($missingMethods)) {
                                return $crypto;
                            }

                            throw new InvalidConfigurationException(\sprintf(
                                'The crypto methods are not supported: "%s".',
                                \implode('", "', $missingMethods)
                            ));
                        })
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function sendmailNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('sendmail');
        $node
            ->children()
                ->booleanNode('lazy')->defaultFalse()->end()
                ->arrayNode('parameters')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            return \preg_split('/\s+/', $v, -1, \PREG_SPLIT_NO_EMPTY);
                        })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
