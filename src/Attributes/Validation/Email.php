<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email implements ValidationAttribute
{
    const RfcValidation           = 1 << 0;
    const NoRfcWarningsValidation = 1 << 1;
    const DnsCheckValidation      = 1 << 2;
    const SpoofCheckValidation    = 1 << 3;
    const FilterEmailValidation   = 1 << 4;

    private int $mode;

    public function __construct(int $mode = self::RfcValidation)
    {
        $this->mode = $mode;
    }

    public function getRules(): array
    {
        $types = [];

        if (self::RfcValidation & $this->mode) {
            $types[] = 'rfc';
        }

        if (self::NoRfcWarningsValidation & $this->mode) {
            $types[] = 'strict';
        }

        if (self::DnsCheckValidation & $this->mode) {
            $types[] = 'dns';
        }

        if (self::SpoofCheckValidation & $this->mode) {
            $types[] = 'spoof';
        }

        if (self::FilterEmailValidation & $this->mode) {
            $types[] = 'filter';
        }

        return [ 'email:' . implode(',', $types) ];
    }
}
