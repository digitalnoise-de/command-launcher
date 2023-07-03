<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

use DateTime;
use DateTimeImmutable;
use Digitalnoise\CommandLauncher\Exception\ParameterNotFound;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

final class LaunchCommand extends Command
{
    /** @var array<string, class-string> */
    private array $commandOptions = [];

    private QuestionHelper $questionHelper;

    public function __construct(
        private readonly CommandProvider $commandProvider,
        private readonly CommandLauncher $commandLauncher,
        /** @var list<ParameterResolver> */
        private readonly array $parameterResolvers
    ) {
        parent::__construct();

        $this->questionHelper = new QuestionHelper();
    }

    protected function configure(): void
    {
        $this->setName('command:launch');
    }

    /**
     * @throws ReflectionException
     * @throws ParameterNotFound
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->buildCommandOptions($this->commandProvider->all());
        $command = $this->chooseCommand($input, $output);

        // Detect command parameters
        $rc                     = new ReflectionClass($command);
        $constructorParams      = $rc->getConstructor()?->getParameters();
        $messageParamAttributes = $rc->getConstructor()->getAttributes(MessageParam::class);

        if (null === $constructorParams) {
            if (!class_exists($command)) {
                throw new RuntimeException(sprintf('Command class "%s" does not exist.', (string)$command));
            }

            // Generate command
            /** @psalm-suppress MixedMethodCall */
            $fullCommand = new $command();

            return $this->handleCommand($fullCommand, $output);
        }

        // Get input for command parameters
        $arguments = $this->commandArguments($constructorParams, $messageParamAttributes, $input, $output);

        // Generate command with payload and execute
        /** @psalm-suppress MixedMethodCall */
        $fullCommand = new $command(...$arguments);

        return $this->handleCommand($fullCommand, $output);
    }

    /**
     * @param list<class-string> $commands
     */
    private function buildCommandOptions(array $commands): void
    {
        if (count($commands) === 0) {
            throw new RuntimeException('No commands could be found.');
        }

        foreach ($commands as $command) {
            $pathParts   = explode('\\', $command);
            $count       = count($pathParts);
            $commandName = $pathParts[$count - 1];

            $this->commandOptions[$commandName] = $command;
        }
    }

    /**
     * @return class-string
     */
    private function chooseCommand(InputInterface $input, OutputInterface $output): string
    {
        $choiceQuestion = new ChoiceQuestion('Choose a command: ', $this->commandOptions);
        $answer         = (string)$this->questionHelper->ask($input, $output, $choiceQuestion);

        return $this->commandOptions[$answer];
    }

    private function handleCommand(object $fullCommand, OutputInterface $output): int
    {
        $this->commandLauncher->launch($fullCommand);

        $output->writeln('<comment>Command successfully executed</comment>');

        return Command::SUCCESS;
    }

    /**
     * @param ReflectionParameter[] $constructorParams
     * @param ReflectionAttribute[] $messageParamAttributes
     *
     * @throws ParameterNotFound
     */
    private function commandArguments(
        array $constructorParams,
        array $messageParamAttributes,
        InputInterface $input,
        OutputInterface $output
    ): array {
        /** @var list<mixed> $arguments */
        $arguments = [];

        $this->messageParamAttributesAreValid($constructorParams, $messageParamAttributes);

        foreach ($constructorParams as $constructorParam) {
            // Simple question should be ok if type is built in
            $type = $constructorParam->getType();
            $name = $constructorParam->getName();

            if (!$type instanceof ReflectionNamedType) {
                throw new RuntimeException('Type error');
            }

            $typeName = $type->getName();

            $attribute = $this->attributeForParam($name, $messageParamAttributes);

            if ($attribute) {
                $arguments[] = $this->handleByAttribute($attribute, $input, $output);

                continue;
            }

            if ($type->isBuiltin() || $typeName === DateTime::class || $typeName === DateTimeImmutable::class) {
                $question = $type->getName() === 'bool'
                    ? new ConfirmationQuestion(sprintf('%s: ', $constructorParam->name))
                    : new Question(sprintf('%s: ', $constructorParam->name));

                /** @psalm-suppress MixedAssignment */
                $answer = $this->questionHelper->ask(
                    $input,
                    $output,
                    $question
                );

                /** @psalm-suppress MixedAssignment */
                $arguments[] = match ($type->getName()) {
                    'string' => (string)$answer,
                    'int' => (int)$answer,
                    'bool' => $answer,
                    'DateTime' => new DateTime((string)$answer),
                    'DateTimeImmutable' => new DateTimeImmutable((string)$answer)
                };

                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $arguments[] = $this->handleCustomParam($constructorParam, $input, $output);
        }

        return $arguments;
    }

    private function handleCustomParam(
        ReflectionParameter $param,
        InputInterface $input,
        OutputInterface $output
    ): mixed {
        $type = $param->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException('Expected named type');
        }
        /** @var class-string $class */
        $class = $type->getName();

        $resolver = $this->resolverForClass($class);

        $options = [];
        foreach ($resolver->allOptions($class) as $item) {
            $options[$item->key] = $item->label;
        }

        $answer = (string)$this->questionHelper->ask($input, $output, new ChoiceQuestion($param->name, $options));

        if ($answer === AbstractParameterResolver::KEY_MANUAL) {
            return $this->manualAnswer($input, $output, $resolver);
        }

        return $resolver->value($answer);
    }

    /**
     * @param class-string $class
     */
    private function resolverForClass(string $class): ParameterResolver
    {
        foreach ($this->parameterResolvers as $resolver) {
            if ($resolver->supports($class)) {
                return $resolver;
            }
        }

        throw new RuntimeException(sprintf('No resolver found for %s', $class));
    }

    /**
     * @param list<ReflectionAttribute> $messageParamAttributes
     */
    private function attributeForParam(string $name, array $messageParamAttributes): ?ReflectionAttribute
    {
        foreach ($messageParamAttributes as $messageParamAttribute) {
            if ($messageParamAttribute->getArguments()['param'] === $name) {
                return $messageParamAttribute;
            }
        }

        return null;
    }

    private function handleByAttribute(
        ReflectionAttribute $attribute,
        InputInterface $input,
        OutputInterface $output
    ): mixed {
        /** @var class-string $class */
        $class = $attribute->getArguments()['resolveClass'];

        $resolver = $this->resolverForClass($class);

        $options = [];
        foreach ($resolver->allOptions($class) as $item) {
            $options[$item->key] = $item->label;
        }

        $answer = (string)$this->questionHelper->ask(
            $input,
            $output,
            new ChoiceQuestion($attribute->getArguments()['param'], $options)
        );

        if ($answer === AbstractParameterResolver::KEY_MANUAL) {
            return $this->manualAnswer($input, $output, $resolver);
        }

        return $resolver->value($answer);
    }

    /**
     * @param ReflectionParameter[] $constructorParams
     * @param ReflectionAttribute[] $messageParamAttributes
     *
     * @throws ParameterNotFound
     */
    private function messageParamAttributesAreValid(array $constructorParams, array $messageParamAttributes)
    {
        $attributeParams   = array_map(fn(ReflectionAttribute $attribute) => $attribute->getArguments()['param'],
            $messageParamAttributes
        );
        $constructorParams = array_map(fn(ReflectionParameter $parameter) => $parameter->getName(), $constructorParams);

        foreach ($attributeParams as $attributeParam) {
            if (!in_array($attributeParam, $constructorParams)) {
                throw ParameterNotFound::forAttributeParam($attributeParam);
            }
        }
    }

    public function manualAnswer(InputInterface $input, OutputInterface $output, ParameterResolver $resolver): mixed
    {
        $manualAnswer = (string)$this->questionHelper->ask($input, $output, new Question('<comment>Manual value: </comment>'));

        return $resolver->manualValue($manualAnswer);
    }
}
