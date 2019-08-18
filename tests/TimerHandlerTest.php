<?php
declare(strict_types=1);

namespace Zwirek\React\Timer\Handler\Tests;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Zwirek\React\Timer\Handler\TimerHandler;

class TimerHandlerTest extends TestCase
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var TimerInterface
     */
    private $timer;

    protected function setUp(): void
    {
        $this->loop = Factory::create();

        $this->timer = $this->loop->addTimer(0.1, function() {
            $this->loop->stop();

            $this->fail('Failsafe stop of loop');
        });
    }

    protected function assertLoop()
    {
        $this->loop->cancelTimer($this->timer);
    }

    /**
     * @test
     */
    public function shouldRunAndRemoveTimer()
    {
        $timerHandler = new TimerHandler($this->loop);

        self::assertTrue($timerHandler->addTimer('test', 0.005, function() {
            $this->assertLoop();
        }));

        $this->loop->run();
    }

    /**
     * @test
     */
    public function shouldNotOverwriteDefinedTimer()
    {
        $timerHandler = new TimerHandler($this->loop);

        self::assertTrue($timerHandler->addTimer('test', 0.005, function() {
            $this->assertLoop();
        }));

        self::assertFalse($timerHandler->addTimer('test', 0.001, function() {
            $this->fail('Timer was overwrite');
        }));

        $this->loop->run();
    }

    /**
     * @test
     */
    public function shouldPassBindParameters()
    {
        $timerHandler = new TimerHandler($this->loop);

        $parameter = 'binded parameter';

        $timerHandler->addTimer('test', 0.005, function() use ($parameter) {
            self::assertEquals('bind parameter', $parameter);

            $this->assertLoop();
        });

        $this->loop->run();
    }

    /**
     * @test
     */
    public function shouldBindParametersByReference()
    {
        $timerHandler = new TimerHandler($this->loop);

        $parameter = null;

        $timerHandler->addTimer('test', 0.005, function() use (&$parameter) {
            $parameter = 'test value';

            $this->assertLoop();
        });

        $this->loop->run();

        self::assertEquals('test value', $parameter);
    }

    /**
     * @test
     */
    public function shouldCancelTimer()
    {
        $timerHandler = new TimerHandler($this->loop);

        $timerHandler->addTimer('timer_to_cancel', 1, function() {});

        $timerHandler->addTimer('test', 0.005, function() use ($timerHandler) {
            self::assertTrue($timerHandler->cancelTimer('timer_to_cancel'));
            $this->assertLoop();
        });

        $this->loop->run();
    }

    /**
     * @test
     */
    public function shouldCancelAllTimers()
    {
        $timerHandler = new TimerHandler($this->loop);

        $timerHandler->addTimer('timer1', 1, function() {});
        $timerHandler->addTimer('timer2', 1, function() {});
        $timerHandler->addTimer('timer3', 1, function() {});

        $timerHandler->addTimer('cancelAll', 0.005, function() use ($timerHandler) {
            self::assertNull($timerHandler->cancelAll());

            $this->assertLoop();
        });

        $this->loop->run();

    }

    /**
     * @test
     */
    public function shouldCallTimerAFewTimes()
    {
        $timerHandler = new TimerHandler($this->loop);

        $counter = 0;

        $timerHandler->addLimitedPeriodicTimer('limited', 0.005, function() use (&$counter) {
            $counter++;

            if ($counter = 5) {
                $this->assertLoop();
            }
        }, 5);

        $this->loop->run();

        self::assertEquals(5, $counter);
    }
}
