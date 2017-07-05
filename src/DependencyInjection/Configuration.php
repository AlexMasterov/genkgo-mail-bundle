<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\{
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

        $treeBuilder->root('genkgo_mail')
            ->beforeNormalization()
                ->ifTrue(static function ($v) {
                    return \is_array($v) && !\array_key_exists('mailers', $v);
                })
                ->then(static function ($v) {
                    $defaultMailer = $v['default_mailer'] ?? 'default';
                    unset($v['default_mailer']);

                    $mailers = $v['mailer'] ?? [$defaultMailer => $v];
                    return [
                        'default_mailer' => $defaultMailer,
                        'mailers' => $mailers,
                    ];
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(static function ($v) {
                    ['default_mailer' => $defaultMailer] = $v;
                    return !isset($v['mailers'][$defaultMailer]);
                })
                ->then(static function ($v) {
                    $v['default_mailer'] = \key($v['mailers']);
                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('default_mailer')->end()
                ->append($this->getMailersNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getMailersNode()
    {
        $node = (new TreeBuilder)->root('mailers');
        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('transport')->defaultValue('smtp')
                        ->validate()
                            ->ifNotInArray(['smtp', 'sendmail', 'null'])
                            ->thenInvalid('The %s transport is not supported.')
                        ->end()
                    ->end()
                    ->scalarNode('host')->defaultValue('localhost')->end()
                    ->scalarNode('port')->defaultNull()->end()
                    ->scalarNode('username')->defaultNull()->end()
                    ->scalarNode('password')->defaultNull()->end()
                    ->scalarNode('auth_mode')->defaultValue('auto')
                    ->validate()
                        ->ifString()
                        ->then(static function ($v) {
                            static $mode = ['none' => 0, 'plain'  => 1, 'login' => 2, 'auto'  => 3];
                            return $mode[$v] ?? $mode['auto'];
                        })
                        ->end()
                    ->end()
                    ->scalarNode('encryption')->defaultNull()
                        ->validate()
                            ->ifNotInArray(['tls', 'ssl', null])
                            ->thenInvalid('The %s encryption is not supported.')
                        ->end()
                    ->end()
                    ->arrayNode('crypto')
                        ->beforeNormalization()->castToArray()->end()
                        ->ignoreExtraKeys(false)
                        ->normalizeKeys(false)
                        ->validate()
                            ->ifTrue(static function ($v) {
                                return \is_array($v) && \count($v) > 0;
                            })
                            ->then(static function ($v) {
                                static $missingMethods = [];
                                foreach ($v as $method) {
                                    $constant = 'STREAM_CRYPTO_METHOD_' . \trim($method);
                                    if (\defined($constant)) {
                                        $v |= \constant($constant);
                                    } else {
                                        $missingMethods[] = $constant;
                                    }
                                }

                                if ($missingMethods) {
                                    throw new InvalidConfigurationException(sprintf(
                                        'The crypto methods are not supported: "%s".',
                                        \implode('", "', $missingMethods)
                                    ));
                                }

                                return $v;
                            })
                        ->end()
                    ->end()
                    ->scalarNode('local_domain')->defaultNull()->end()
                    ->scalarNode('timeout')->defaultValue(30)->end()
                    ->scalarNode('reconnect_after')->defaultNull()->end()
                    ->scalarNode('retry')->defaultNull()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
