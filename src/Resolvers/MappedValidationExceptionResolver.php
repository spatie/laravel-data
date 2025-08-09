<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class MappedValidationExceptionResolver
{
    public function execute(
        ValidationException $exception,
        array $mappedProperties,
    ): ValidationException {
        if (! $exception->validator instanceof Validator) {
            return $exception;
        }

        $messages = $exception->validator->errors();

        foreach ($messages->getMessages() as $key => $messagesList) {
            foreach ($mappedProperties as $original => $mapped) {
                if ($key === $original) {
                    $this->mapEntryInMessageBag(
                        $messages,
                        original: $key,
                        mapped: $mapped,
                        messagesList: $messagesList
                    );

                    continue;
                }

                if (str_starts_with($key, "{$original}.")) {
                    $newKey = str_replace("{$original}.", "{$mapped}.", $key);

                    $this->mapEntryInMessageBag(
                        $messages,
                        original: $key,
                        mapped: $newKey,
                        messagesList: $messagesList
                    );

                    continue;
                }
            }
        }

        return $exception;
    }

    protected function mapEntryInMessageBag(
        MessageBag $messageBag,
        string $original,
        string $mapped,
        array $messagesList
    ): void {
        foreach ($messagesList as $message) {
            $messageBag->add($mapped, $message);
        }

        $messageBag->forget($original);
    }
}
