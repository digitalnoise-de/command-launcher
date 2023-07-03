<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher;

use DateTime;
use DateTimeImmutable;
use Digitalnoise\CommandLauncher\CommandLauncher;
use Digitalnoise\CommandLauncher\CommandProvider;
use Digitalnoise\CommandLauncher\Exception\ParameterNotFound;
use Digitalnoise\CommandLauncher\LaunchCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Digitalnoise\CommandLauncher\Command\BoolCommand;
use Tests\Digitalnoise\CommandLauncher\Command\DateTimeCommand;
use Tests\Digitalnoise\CommandLauncher\Command\DateTimeImmutableCommand;
use Tests\Digitalnoise\CommandLauncher\Command\IntCommand;
use Tests\Digitalnoise\CommandLauncher\Command\ManualInputCommand;
use Tests\Digitalnoise\CommandLauncher\Command\StringCommand;
use Tests\Digitalnoise\CommandLauncher\Command\StringCommandWithAttribute;
use Tests\Digitalnoise\CommandLauncher\Command\StringCommandWithWrongAttribute;
use Tests\Digitalnoise\CommandLauncher\Model\ManualInput;
use Tests\Digitalnoise\CommandLauncher\ParameterResolver\PersonResolver;

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
            new DateTimeImmutableCommand(new DateTimeImmutable('2022-01-01 00:00')),
        ];
    }

    public function launch(object $command): void
    {
        $this->launchedCommands[] = $command;
    }

    /**
     * @test
     */
    public function it_should_handle_message_attributes(): void
    {
        $test = new CommandTester(new LaunchCommand($this, $this, [new PersonResolver()]));
        $test->setInputs(['StringCommandWithAttribute', 'jane']);
        $test->execute([]);

        self::assertEquals([new StringCommandWithAttribute('success')], $this->launchedCommands);
    }

    /**
     * @test
     */
    public function it_should_show_a_custom_input_option(): void
    {
        $test = new CommandTester(new LaunchCommand($this, $this, [new PersonResolver()]));
        $input  = 'A very cool manual input';
        $test->setInputs(['ManualInputCommand', 'manual', $input]);
        $test->execute([]);

        $result = $test->getDisplay();

        self::assertStringContainsString('[manual] Manual input', $result);
        self::assertEquals([new ManualInputCommand(ManualInput::fromString($input))], $this->launchedCommands);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_attribute_param_does_not_exist(): void
    {
        $test = new CommandTester(new LaunchCommand($this, $this, [new PersonResolver()]));

        self::expectExceptionObject(ParameterNotFound::forAttributeParam('inpu'));

        $test->setInputs(['StringCommandWithWrongAttribute', 'jane']);
        $test->execute([]);
    }

    public function all(): array
    {
        return [
            StringCommand::class,
            IntCommand::class,
            BoolCommand::class,
            DateTimeCommand::class,
            DateTimeImmutableCommand::class,
            StringCommandWithAttribute::class,
            StringCommandWithWrongAttribute::class,
            ManualInputCommand::class
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
