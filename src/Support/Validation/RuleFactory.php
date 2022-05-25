<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Validation\Accepted;
use Spatie\LaravelData\Attributes\Validation\AcceptedIf;
use Spatie\LaravelData\Attributes\Validation\ActiveUrl;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Alpha;
use Spatie\LaravelData\Attributes\Validation\AlphaDash;
use Spatie\LaravelData\Attributes\Validation\AlphaNumeric;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Bail;
use Spatie\LaravelData\Attributes\Validation\Before;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\CurrentPassword;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateEquals;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Digits;
use Spatie\LaravelData\Attributes\Validation\DigitsBetween;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Distinct;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\EndsWith;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\ExcludeIf;
use Spatie\LaravelData\Attributes\Validation\ExcludeUnless;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Filled;
use Spatie\LaravelData\Attributes\Validation\GreaterThan;
use Spatie\LaravelData\Attributes\Validation\GreaterThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\Image;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\InArray;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\IP;
use Spatie\LaravelData\Attributes\Validation\IPv4;
use Spatie\LaravelData\Attributes\Validation\IPv6;
use Spatie\LaravelData\Attributes\Validation\Json;
use Spatie\LaravelData\Attributes\Validation\LessThan;
use Spatie\LaravelData\Attributes\Validation\LessThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\MimeTypes;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\MultipleOf;
use Spatie\LaravelData\Attributes\Validation\NotIn;
use Spatie\LaravelData\Attributes\Validation\NotRegex;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Prohibited;
use Spatie\LaravelData\Attributes\Validation\ProhibitedIf;
use Spatie\LaravelData\Attributes\Validation\ProhibitedUnless;
use Spatie\LaravelData\Attributes\Validation\Prohibits;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Attributes\Validation\RequiredUnless;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\RequiredWithAll;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\RequiredWithoutAll;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Same;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StartsWith;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Timezone;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Exceptions\CouldNotCreateValidationRule;

class RuleFactory
{
    public function create(string $rule): ValidationRule
    {
        $keyword = Str::before($rule, ':');
        $parameters = str_contains($rule, ':')
            ? $this->resolveParameters(Str::after($rule, ':'))
            : [];

        /** @var \Spatie\LaravelData\Attributes\Validation\StringValidationAttribute|\Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute|null $ruleClass */
        $ruleClass = $this->mapping()[$keyword] ?? null;

        if ($ruleClass === null) {
            throw CouldNotCreateValidationRule::create($rule);
        }

        return $ruleClass::create(...$parameters);
    }

    private function resolveParameters(string $parameters): array
    {
        if (empty($parameters)) {
            return [];
        }

        return collect(explode(',', $parameters))->mapWithKeys(
            fn (string $parameter, int $index) => str_contains($parameter, '=')
            ? [Str::before($parameter, '=') => Str::after($parameter, '=')]
            : [$index => $parameter]
        )->all();
    }

    private function mapping(): array
    {
        return [
            Accepted::keyword() => Accepted::class,
            AcceptedIf::keyword() => AcceptedIf::class,
            ActiveUrl::keyword() => ActiveUrl::class,
            After::keyword() => After::class,
            AfterOrEqual::keyword() => AfterOrEqual::class,
            Alpha::keyword() => Alpha::class,
            AlphaDash::keyword() => AlphaDash::class,
            AlphaNumeric::keyword() => AlphaNumeric::class,
            ArrayType::keyword() => ArrayType::class,
            Bail::keyword() => Bail::class,
            Before::keyword() => Before::class,
            BeforeOrEqual::keyword() => BeforeOrEqual::class,
            Between::keyword() => Between::class,
            BooleanType::keyword() => BooleanType::class,
            Confirmed::keyword() => Confirmed::class,
            CurrentPassword::keyword() => CurrentPassword::class,
            Date::keyword() => Date::class,
            DateEquals::keyword() => DateEquals::class,
            DateFormat::keyword() => DateFormat::class,
            Different::keyword() => Different::class,
            Digits::keyword() => Digits::class,
            DigitsBetween::keyword() => DigitsBetween::class,
            Dimensions::keyword() => Dimensions::class,
            Distinct::keyword() => Distinct::class,
            Email::keyword() => Email::class,
            EndsWith::keyword() => EndsWith::class,
            Enum::keyword() => Enum::class,
            ExcludeIf::keyword() => ExcludeIf::class,
            ExcludeUnless::keyword() => ExcludeUnless::class,
            Exists::keyword() => Exists::class,
            File::keyword() => File::class,
            Filled::keyword() => Filled::class,
            GreaterThan::keyword() => GreaterThan::class,
            GreaterThanOrEqualTo::keyword() => GreaterThanOrEqualTo::class,
            Image::keyword() => Image::class,
            In::keyword() => In::class,
            InArray::keyword() => InArray::class,
            IntegerType::keyword() => IntegerType::class,
            IP::keyword() => IP::class,
            IPv4::keyword() => IPv4::class,
            IPv6::keyword() => IPv6::class,
            Json::keyword() => Json::class,
            LessThan::keyword() => LessThan::class,
            LessThanOrEqualTo::keyword() => LessThanOrEqualTo::class,
            Max::keyword() => Max::class,
            Mimes::keyword() => Mimes::class,
            MimeTypes::keyword() => MimeTypes::class,
            Min::keyword() => Min::class,
            MultipleOf::keyword() => MultipleOf::class,
            NotIn::keyword() => NotIn::class,
            NotRegex::keyword() => NotRegex::class,
            Nullable::keyword() => Nullable::class,
            Numeric::keyword() => Numeric::class,
            Password::keyword() => Password::class,
            Present::keyword() => Present::class,
            Prohibited::keyword() => Prohibited::class,
            ProhibitedIf::keyword() => ProhibitedIf::class,
            ProhibitedUnless::keyword() => ProhibitedUnless::class,
            Prohibits::keyword() => Prohibits::class,
            Regex::keyword() => Regex::class,
            Required::keyword() => Required::class,
            RequiredIf::keyword() => RequiredIf::class,
            RequiredUnless::keyword() => RequiredUnless::class,
            RequiredWith::keyword() => RequiredWith::class,
            RequiredWithAll::keyword() => RequiredWithAll::class,
            RequiredWithout::keyword() => RequiredWithout::class,
            RequiredWithoutAll::keyword() => RequiredWithoutAll::class,
//            Rule::keyword() => Rule::class,
            Same::keyword() => Same::class,
            Size::keyword() => Size::class,
            Sometimes::keyword() => Sometimes::class,
            StartsWith::keyword() => StartsWith::class,
            StringType::keyword() => StringType::class,
            Timezone::keyword() => Timezone::class,
            Unique::keyword() => Unique::class,
            Url::keyword() => Url::class,
            Uuid::keyword() => Uuid::class,
        ];
    }
}
