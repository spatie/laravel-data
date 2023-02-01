<?php

namespace Spatie\LaravelData\Tests\TestSupport;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationRuleParser;

use function PHPUnit\Framework\assertTrue;

use Spatie\LaravelData\Data;

use Spatie\LaravelData\DataPipeline;

use Spatie\LaravelData\DataPipes\MapPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\ValidatePropertiesDataPipe;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Support\Validation\DataRules;

use Spatie\LaravelData\Support\Validation\ValidationPath;

/**
 * @property class-string<Data::class> $dataClass
 */
class DataValidationAsserter
{
    private readonly string $dataClass;

    public static function for(
        string|object $dataClass
    ): self {
        return new self($dataClass);
    }

    public function __construct(
        string|object $dataClass,
    ) {
        $this->dataClass = is_object($dataClass)
            ? $dataClass::class
            : $dataClass;
    }

    public function assertOk(array $payload): self
    {
        $this->dataClass::validate(
            $this->pipePayload($payload)
        );

        expect(true)->toBeTrue();

        return $this;
    }

    public function assertErrors(
        array $payload,
        ?array $errors = null
    ): self {
        try {
            $this->dataClass::validate(
                $this->pipePayload($payload)
            );
        } catch (ValidationException $exception) {
            expect(true)->toBeTrue();

            if ($errors !== null) {
                expect($exception->errors())->toBe($errors);
            }

            return $this;
        }

        assertTrue(false, 'No validation errors');

        return $this;
    }

    public function assertRules(
        array $rules,
        array $payload = []
    ): self {
        $inferredRules = app(DataValidationRulesResolver::class)->execute(
            $this->dataClass,
            $this->pipePayload($payload),
            ValidationPath::create(),
            DataRules::create()
        );

        $parser = new ValidationRuleParser($payload);

        expect($parser->explode($inferredRules)->rules)->toEqual($rules);

        return $this;
    }

    public function assertRedirect(array $payload, string $redirect): self
    {
        try {
            $this->dataClass::validate(
                $this->pipePayload($payload)
            );
        } catch (ValidationException $exception) {
            expect($exception->redirectTo)->toBe($redirect);

            return $this;
        }

        assertTrue(false, 'No validation errors');

        return $this;
    }

    public function assertErrorBag(array $payload, string $errorBag): self
    {
        try {
            $this->dataClass::validate(
                $this->pipePayload($payload)
            );
        } catch (ValidationException $exception) {
            expect($exception->errorBag)->toBe($errorBag);

            return $this;
        }

        assertTrue(false, 'No validation errors');

        return $this;
    }

    public function assertMessages(
        array $messages,
        array $payload = []
    ): self {
        $validator = app(DataValidatorResolver::class)->execute(
            $this->dataClass,
            $this->pipePayload($payload),
        );

        expect($validator->customMessages)->toEqual($messages);

        return $this;
    }

    public function assertAttributes(
        array $attributes,
        array $payload = []
    ): self {
        $validator = app(DataValidatorResolver::class)->execute(
            $this->dataClass,
            $this->pipePayload($payload),
        );

        expect($validator->customAttributes)->toEqual($attributes);

        return $this;
    }

    private function pipePayload(array $payload): array
    {
        $properties = app(DataPipeline::class)
            ->using($payload)
            ->normalizer(ArrayNormalizer::class)
            ->into($this->dataClass)
            ->through(MapPropertiesDataPipe::class)
            ->through(ValidatePropertiesDataPipe::class)
            ->execute();

        return $properties->all();
    }
}
