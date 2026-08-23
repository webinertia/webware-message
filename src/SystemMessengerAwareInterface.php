<?php

declare(strict_types=1);

namespace Webware\Message;

/**
 * @api
 */
interface SystemMessengerAwareInterface
{
    public function getSystemMessenger(): SystemMessengerInterface;
}
