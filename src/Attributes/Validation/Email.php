<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email extends StringValidationAttribute
{
    public const RfcValidation = 'rfc';
    public const NoRfcWarningsValidation = 'strict';
    public const DnsCheckValidation = 'dns';
    public const SpoofCheckValidation = 'spoof';
    public const FilterEmailValidation = 'filter';

    private array $modes;

    public function __construct(array | string ...$modes)
    {
        $this->modes = Arr::flatten($modes);
    }

    public static function keyword(): string
    {
        return 'email';
    }

    public function parameters(): array
    {
        return collect($this->modes)
            ->whenEmpty(fn (Collection $modes) => $modes->add(self::RfcValidation))
            ->filter(fn (string $mode) => in_array($mode, [
                self::RfcValidation,
                self::NoRfcWarningsValidation,
                self::DnsCheckValidation,
                self::SpoofCheckValidation,
                self::FilterEmailValidation,
            ]))
            ->whenEmpty(fn () => throw CannotBuildValidationRule::create("Email validation rule needs at least one valid mode."))
            ->all();
    }
}
