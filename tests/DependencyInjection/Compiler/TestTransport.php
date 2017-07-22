<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Tests\DependencyInjection\Compiler;

use Genkgo\Mail\{
    MessageInterface,
    TransportInterface
};

class TestTransport implements TransportInterface
{
    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message): void
    {
        return;
    }
}
