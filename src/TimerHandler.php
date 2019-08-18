<?php
declare(strict_types=1);

namespace Zwirek\Reactphp\Timer\Handler;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

final class TimerHandler implements TimerHandlerInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var TimerInterface[]
     */
    private $timers = [];

    /**
     * @var int[]
     */
    private $limitedPeriodicTimerCounters = [];

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @throws ValidationException
     */
    public function addTimer(string $name, $interval, callable $callback): bool
    {
        $this->validate($name);

        if (array_key_exists($name, $this->timers)) {
            return false;
        }

        $this->timers[$name] = $this->loop->addTimer($interval, function (TimerInterface $timer) use ($callback, $name) {
            $this->timers[$name] = null;
            unset($this->timers[$name]);

            call_user_func($callback, $timer);
        });

        return true;
    }

    /**
     * @throws ValidationException
     */
    public function addPeriodicTimer(string $name, $interval, callable $callback): bool
    {
        $this->validate($name);

        if (array_key_exists($name, $this->timers)) {
            return false;
        }

        $this->timers[$name] = $this->loop->addPeriodicTimer($interval, $callback);

        return true;
    }

    /**
     * @throws ValidationException
     */
    public function cancelTimer(string $name): bool
    {
        $this->validate($name);

        if (array_key_exists($name, $this->timers)) {
            $this->loop->cancelTimer($this->timers[$name]);
            $this->timers[$name] = null;
            unset($this->timers[$name]);

            return true;
        }

        return false;
    }

    public function cancelAll(): void
    {
        foreach($this->timers as $timer) {
            $this->loop->cancelTimer($timer);
        }

        $this->timers = [];
    }

    /**
     * @throws ValidationException
     */
    public function addLimitedPeriodicTimer(string $name, $interval, callable $callback, int $callLimit = 1): bool
    {
        $this->validate($name);

        if (array_key_exists($name, $this->timers)) {
            return false;
        }

        $this->limitedPeriodicTimerCounters[$name] = 0;

        $this->timers[$name] = $this->loop->addPeriodicTimer($interval, function (TimerInterface $timer) use ($callback, $name, $callLimit) {
            call_user_func($callback, $timer);

            if (++$this->limitedPeriodicTimerCounters >= $callLimit) {
                $this->loop->cancelTimer($timer);
                $this->timers[$name] = null;
                unset($this->timers[$name]);
            }
        });

        return true;
    }

    /**
     * @throws ValidationException
     */
    private function validate(string $name): void
    {
        $regex = '[a-zA-Z0-9_-]+';
        if (!preg_match('#' . $regex . '#', $name)) {
            throw new ValidationException(sprintf('Invalid name of timer. Name must be validated by regex %s', $regex));
        }
    }
}
