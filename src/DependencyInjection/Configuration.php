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
        $rootNode = $treeBuilder->root('genkgo_mail');

        $rootNode
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
                    return empty($v['mailers'][$v['default_mailer']]);
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
                ->beforeNormalization()
                    ->always()
                    ->then(static function ($v) {
                        $v['crypto'] = $v['crypto'] ?? [];
                        return $v;
                    })
                ->end()
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
                    ->append($this->getCryptoNode())
                    ->scalarNode('local_domain')->defaultNull()->end()
                    ->scalarNode('timeout')->defaultValue(30)->end()
                    ->scalarNode('reconnect_after')->defaultNull()->end()
                    ->scalarNode('retry')->defaultNull()->end()
                    ->booleanNode('lazy')->defaultFalse()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getCryptoNode()
    {
        $node = (new TreeBuilder)->root('crypto');
        $node
            ->prototype('scalar')->end()
            ->validate()
                ->always()
                ->then(static function ($v) {
                    static $missingMethods = [];
                    foreach ($v as $method) {
                        $method = \trim((string) $method);
                        $constant = "STREAM_CRYPTO_METHOD_{$method}";
                        if (\defined($constant)) {
                            $v |= \constant($constant);
                        } else {
                            $missingMethods[] = $constant;
                        }
                    }

                    if (empty($missingMethods)) {
                        return $v;
                    }

                    throw new InvalidConfigurationException(sprintf(
                        'The crypto methods are not supported: "%s".',
                        \implode('", "', $missingMethods)
                    ));
                })
            ->end()
            ->validate()
                ->ifEmpty()
                ->then(static function ($v) {
                    return null;
                })
            ->end()
        ;

        return $node;
    }
}
