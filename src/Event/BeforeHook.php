<?php declare(strict_types=1);

namespace Shyim\Hooks\Event;

use Shyim\Hooks\Exception\InvalidArgumentIndexException;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeHook extends Event
{
    /**
     * @var mixed
     */
    private $return;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var object
     */
    private $subject;

    public function __construct(array $arguments, object $subject)
    {
        $this->arguments = $arguments;
        $this->subject = $subject;
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

    public function hasArgument(int $index): bool
    {
        return array_key_exists($index, $this->arguments);
    }

    public function getArgument(int $index)
    {
        if ($this->hasArgument($index)) {
            return $this->arguments[$index];
        }

        throw new InvalidArgumentIndexException(sprintf('Index %d does not exists', $index));
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
