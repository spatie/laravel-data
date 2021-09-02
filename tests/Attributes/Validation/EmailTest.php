<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Tests\TestCase;

class EmailTest extends TestCase
{

    /** @test */
    public function it_can_get_the_correct_rules_for_the_attribute()
    {
        $this->assertValidationAttributeRules(
            ['email:rfc'],
            new Email()
        );

        $this->assertValidationAttributeRules(
            ['email:rfc'],
            new Email(Email::RfcValidation)
        );

        $this->assertValidationAttributeRules(
            ['email:rfc,strict'],
            new Email([Email::RfcValidation, Email::NoRfcWarningsValidation])
        );

        $this->assertValidationAttributeRules(
            ['email:rfc,strict,dns,spoof,filter'],
            new Email([Email::RfcValidation, Email::NoRfcWarningsValidation, Email::DnsCheckValidation, Email::SpoofCheckValidation, Email::FilterEmailValidation])
        );
    }

    /** @test */
    public function it_fails_with_other_modes()
    {
        $this->expectException(CannotBuildValidationRule::class);

        (new Email(['fake']))->getRules();
    }
}
