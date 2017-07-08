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
                    return !isset($value['mailers'][$value['default_mailer']]);
                })
                ->thenInvalid('"default_mailer" not found.')
            ->end()
            ->children()
                ->scalarNode('default_mailer')->end()
                ->append($this->mailersNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function normalizeRootNode(ArrayNodeDefinition $rootNode)
    {
        $normalizer = static function ($value) {
            $defaultMailer = $value['default_mailer'] ?? 'default';
            unset($value['default_mailer']);

            return [
                'default_mailer' => $defaultMailer,
                'mailers' => $value['mailers'] ?? [$defaultMailer => $value],
            ];
        };

        $rootNode->beforeNormalization()->always()->then($normalizer)->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function mailersNode()
    {
        $node = (new TreeBuilder)->root('mailers');
        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->enumNode('transport')->defaultValue('smtp')
                        ->treatNullLike('null')
                        ->values(['smtp', 'sendmail', 'null'])
                    ->end()
                    ->scalarNode('host')->defaultValue('localhost')->end()
                    ->scalarNode('port')->defaultNull()->end()
                    ->scalarNode('username')->defaultNull()->end()
                    ->scalarNode('password')->defaultNull()->end()
                    ->enumNode('auth_mode')->defaultValue('auto')
                        ->values(['none', 'plain', 'login', 'auto'])
                    ->end()
                    ->enumNode('encryption')->defaultNull()
                        ->values(['tls', 'ssl', null])
                    ->end()
                    ->scalarNode('crypto')->defaultNull()
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
                    ->scalarNode('local_domain')->defaultNull()->end()
                    ->scalarNode('timeout')->defaultValue(30)->end()
                    ->scalarNode('reconnect_after')->defaultNull()->end()
                    ->scalarNode('retry')->defaultNull()->end()
                    ->booleanNode('lazy')->defaultFalse()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
