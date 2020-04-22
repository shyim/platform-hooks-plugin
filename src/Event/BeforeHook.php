<?php declare(strict_types=1);

namespace Shyim\Hooks\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeHook extends Event
{
    /**
     * @var mixed
     */
    private $return;
    private $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    private $hasReturn = false;

    public function getReturn()
    {
        return $this->return;
    }

    public function setReturn($return)
    {
        $this->hasReturn = true;
        $this->return = $return;
    }

    public function hasReturn(): bool
    {
        return $this->hasReturn;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
