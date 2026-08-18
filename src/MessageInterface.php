<?php

declare(strict_types=1);

namespace Webware\Message;

use JsonSerializable;
use Stringable;

interface MessageInterface extends JsonSerializable, Stringable
{
    public function getMessage(): string;
}
