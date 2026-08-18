<?php

declare(strict_types=1);

namespace Webware\Message;

trait MessageLevelCapableTrait
{
    protected ?MessageLevel $messageLevel = null;

    public function getMessageLevel(): MessageLevel
    {
        return $this->messageLevel ?? MessageLevel::Info;
    }

    public function setMessageLevel(MessageLevel $messageLevel): static
    {
        $this->messageLevel = $messageLevel;
        return $this;
    }
}
