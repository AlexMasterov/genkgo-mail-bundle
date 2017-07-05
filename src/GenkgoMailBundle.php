<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle;

use AlexMasterov\GenkgoMailBundle\DependencyInjection\Compiler\SmtpClientPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GenkgoMailBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SmtpClientPass());
    }
}
