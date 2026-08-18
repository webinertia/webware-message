<?php

declare(strict_types=1);

namespace Webware\Message;

interface SystemMessengerAwareInterface
{
    public function getSystemMessenger(): SystemMessengerInterface;
}
