<?php
declare(strict_types=1);

namespace Zwirek\React\Timer\Handler;

interface TimerHandlerInterface
{
    /**
     * @throws ValidationException
     */
    public function addTimer(string $name, $interval, callable $callback): bool;

    /**
     * @throws ValidationException
     */
    public function addPeriodicTimer(string $name, $interval, callable $callback): bool;

    /**
     * @throws ValidationException
     */
    public function cancelTimer(string $name): bool;

    public function cancelAll(): void;

    /**
     * @throws ValidationException
     */
    public function addLimitedPeriodicTimer(string $name, $interval, callable $callback, int $callLimit = 1): bool;
}