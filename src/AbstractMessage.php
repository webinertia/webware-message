<?php

declare(strict_types=1);

namespace Webware\Message;

use Override;

/**
 * @internal
 */
abstract class AbstractMessage implements MessageInterface
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    #[Override]
    public function getMessage(): string
    {
        return $this->message;
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        return $this->getMessage();
    }

    #[Override]
    public function __toString(): string
    {
        return $this->getMessage();
    }
}
