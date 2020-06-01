# Magneto 2 Stopwatch

Experiment using Symfony timer component and magento console command. For monitoring performance of console commands.

https://symfony.com/doc/current/components/stopwatch.html

## Install

    composer require symfony/stopwatch

## Usage

```php
[...]
use Symfony\Component\Stopwatch\Stopwatch;
[...]
```

```php
$stopwatch = new Stopwatch();
// starts event named 'eventName'
$stopwatch->start('eventName');
// ... run your code here
$event = $stopwatch->stop('eventName');
// you can convert $event into a string for a quick summary
// e.g. (string) $event = '4.50 MiB - 26 ms'
$event->getCategory();   // returns the category the event was started in
$event->getOrigin();     // returns the event start time in milliseconds
$event->ensureStopped(); // stops all periods not already stopped
$event->getStartTime();  // returns the start time of the very first period
$event->getEndTime();    // returns the end time of the very last period
$event->getDuration();   // returns the event duration, including all periods
$event->getMemory();     // returns the max memory usage of all periods
```

### Sample Console script

```php
/**
 * {@inheritdoc}
 */
protected function execute(
    InputInterface $input,
    OutputInterface $output
) {
    $this->state->setAreaCode(Area::AREA_GLOBAL);
    $output->writeln('<info>Start</info>');

    $stopwatch = new Stopwatch();
    $stopwatch->start('stopwatch');

    // lengthy process

    $event = $stopwatch->stop('stopwatch');
    $output->writeln((string) $event);
    $output->writeln((string) __("Start : %1", date("d-m-Y H:i:s", (int) ($event->getOrigin() / 1000))));
    $output->writeln((string) __("End : %1", date("d-m-Y H:i:s", (int) (($event->getOrigin() + $event->getEndTime()) / 1000))));
    $output->writeln((string) __("Memory : %1 MiB", $event->getMemory() / 1024 / 1024));
    $output->writeln('<info>Finish</info>');
}
```