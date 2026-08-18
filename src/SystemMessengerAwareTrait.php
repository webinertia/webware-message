<?php

declare(strict_types=1);

namespace Webware\Message;

trait SystemMessengerAwareTrait
{
    protected SystemMessengerInterface $systemMessenger;

    public function getSystemMessenger(): SystemMessengerInterface
    {
        return $this->systemMessenger;
    }

    public function setSystemMessenger(SystemMessengerInterface $systemMessenger): static
    {
        $this->systemMessenger = $systemMessenger;

        return $this;
    }
}
