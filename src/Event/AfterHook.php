<?php declare(strict_types=1);

namespace Shyim\Hooks\Event;

class AfterHook extends BeforeHook
{
    public function __construct(array $arguments, object $subject, $return)
    {
        parent::__construct($arguments, $subject);
        $this->setReturn($return);
    }
}
