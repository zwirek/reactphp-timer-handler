# ReactPHP Timer Handler

When `react/event-loop` timer is set, method will return object of the timer. It is important to hold reference to timer
when you want to cancel this timer. But due to the nature of asynchronous live cycle of the process it is likely that reference 
will be overwritten by the new timer. ReactPHP Timer Handler introduce different approach to handle timers. Each timer
has its unique name.

## Usage examples

Add timer

```

$loop = \React\EventLoop\Factory::create();
$handler = new \Zwirek\Reactphp\Timer\Handler\TimerHandler($loop);

$success = $handler->addTimer('example_timer', 1, function (React\EventLoop\Timer\Timer $timer) {
    echo 'example_timer', PHP_EOL;
});

var_dump($success); //prints true

$loop->run();

echo 'done', PHP_EOL;

```

You can not overwrite your timer accidentally. When run, you will see output from the first timer only.

```
$loop = \React\EventLoop\Factory::create();
$handler = new \Zwirek\Reactphp\Timer\Handler\TimerHandler($loop);

$first = $handler->addTimer('example_timer', 1, function (React\EventLoop\Timer\Timer $timer) {
    echo 'first', PHP_EOL;
});

$second = $handler->addTimer('example_timer', 1, function (React\EventLoop\Timer\Timer $timer) {
    echo 'second', PHP_EOL;
});

var_dump($first); //prints true
var_dump($second); //prints false

$loop->run();

echo 'done', PHP_EOL;

```

Timer can be canceled by its name
```
$loop = \React\EventLoop\Factory::create();
$handler = new \Zwirek\Reactphp\Timer\Handler\TimerHandler($loop);

$timer = $handler->addTimer('example_timer', 1, function (React\EventLoop\Timer\Timer $timer) {
    echo 'example_timer', PHP_EOL;
});

$handler->cancelTimer('example_timer');

$loop->run();

echo 'done', PHP_EOL;
```

`react/event-loop` allow to register periodic timer. TimerHandler also allow to register periodic timer by name
```
$loop = \React\EventLoop\Factory::create();

$handler = new \Zwirek\Reactphp\Timer\Handler\TimerHandler($loop);

$timer = $handler->addPeriodicTimer('periodic_timer', 1, function (React\EventLoop\Timer\Timer $timer) {});

$loop->run();
```

It is possible to create periodic timer which will be executed limited number of times
```
$loop = \React\EventLoop\Factory::create();

$handler = new \Zwirek\Reactphp\Timer\Handler\TimerHandler($loop);

$timer = $handler->addLimitedPeriodicTimer('limited_periodic_timer', 1, function (React\EventLoop\Timer\Timer $timer) {}, 5);

$loop->run();
```
Above timer execute registered handler 5 times at intervals of 1 seconds

To cancel all registered times just simply run:
```
$handler->cancelAll();
```


