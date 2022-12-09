<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher;

use DateTime;
use DateTimeImmutable;
use Digitalnoise\CommandLauncher\CommandLauncher;
use Digitalnoise\CommandLauncher\CommandProvider;
use Digitalnoise\CommandLauncher\LaunchCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Digitalnoise\CommandLauncher\Command\BoolCommand;
use Tests\Digitalnoise\CommandLauncher\Command\DateTimeCommand;
use Tests\Digitalnoise\CommandLauncher\Command\DateTimeImmutableCommand;
use Tests\Digitalnoise\CommandLauncher\Command\IntCommand;
use Tests\Digitalnoise\CommandLauncher\Command\StringCommand;

/**
 * @covers \Digitalnoise\CommandLauncher\LaunchCommand
 */
final class LaunchCommandTest extends TestCase implements CommandProvider, CommandLauncher
{
    private array $launchedCommands = [];

    /**
     * @test
     * @dataProvider scalarExamples
     */
    public function it_should_map_scalar_types(string $command, string $input, object $expected): void
    {
        $test = new CommandTester(new LaunchCommand($this, $this, []));
        $test->setInputs([$command, $input]);
        $test->execute([]);

        self::assertEquals([$expected], $this->launchedCommands);
    }

    public function scalarExamples(): iterable
    {
        yield ['StringCommand', 'value', new StringCommand('value')];
        yield ['IntCommand', '123', new IntCommand(123)];
        yield ['BoolCommand', 'yes', new BoolCommand(true)];
        yield ['BoolCommand', 'no', new BoolCommand(false)];
        yield ['DateTimeCommand', '2022-01-01 00:00', new DateTimeCommand(new DateTime('2022-01-01 00:00'))];
        yield [
            'DateTimeImmutableCommand',
            '2022-01-01 00:00',
            new DateTimeImmutableCommand(new DateTimeImmutable('2022-01-01 00:00'))
        ];
    }

    public function launch(object $command): void
    {
        $this->launchedCommands[] = $command;
    }

    public function all(): array
    {
        return [
            StringCommand::class,
            IntCommand::class,
            BoolCommand::class,
            DateTimeCommand::class,
            DateTimeImmutableCommand::class
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
