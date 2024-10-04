<?php declare(strict_types=1);

namespace Shyim\Hooks\Event;

use Shyim\Hooks\Exception\InvalidArgumentIndexException;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeHook extends Event
{
    private mixed $return;

    private array $arguments;

    private object $subject;

    private bool $hasReturn = false;

    public function __construct(array $arguments, object $subject)
    {
        $this->arguments = $arguments;
        $this->subject = $subject;
    }

    public function getReturn(): object
    {
        return $this->return;
    }

    public function setReturn(object $return): void
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

    public function hasArgument(int $index): bool
    {
        return \array_key_exists($index, $this->arguments);
    }

    public function getArgument(int $index)
    {
        if ($this->hasArgument($index)) {
            return $this->arguments[$index];
        }

        throw new InvalidArgumentIndexException(\sprintf('Index %d does not exists', $index));
    }

    public function setArgument(int $index, $value): void
    {
        $this->arguments[$index] = $value;
    }

    public function getSubject(): object
    {
        return $this->subject;
    }
}
