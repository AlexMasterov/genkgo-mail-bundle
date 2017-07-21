<?php
declare(strict_types=1);

namespace AlexMasterov\GenkgoMailBundle\Transport;

use Closure;
use Genkgo\Mail\{
    MessageInterface,
    TransportInterface
};

class LazyTransport implements TransportInterface
{
    /**
     * @param Closure $closure
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message): void
    {
        $this->getTransport()->send($message);
    }

    /**
     * @var Closure
     */
    private $closure;

    /**
     * @var TransportInterface
     */
    private $decoratedTransport = null;

    /**
     * @return TransportInterface
     */
    private function getTransport(): TransportInterface
    {
        return $this->decoratedTransport
            ?? $this->decoratedTransport = ($this->closure)();
    }
}
