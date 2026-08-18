<?php

declare(strict_types=1);

namespace Webware\Message;

trait MessageLevelCapableTrait
{
    public function getMessageLevel(): MessageLevel
    {
        return $this->messageLevel ?? MessageLevel::Info;
    }

    public function setMessageLevel(MessageLevel $messageLevel): MessageLevelCapableInterface
    {
        $this->messageLevel = $messageLevel;
        return $this;
    }
}
