<?php

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use Spatie\LaravelData\Resolvers\ContextResolver;

it('can resolve context from property', function () {
    $resolver = new ContextResolver();

    // Create a ReflectionProperty for the test class
    $reflectionProperty = new ReflectionProperty(TestContextResolverClass::class, 'testProperty');

    // Resolve the context
    $context = $resolver->execute($reflectionProperty);

    // Create expected context
    $expectedContext = (new ContextFactory())->createFromReflector($reflectionProperty->getDeclaringClass());

    // Assertions
    expect($context)->toBeInstanceOf(Context::class);
    expect($context)->toEqual($expectedContext);
});

it('can resolve context from class', function () {
    $resolver = new ContextResolver();

    // Create a ReflectionClass for the test class
    $reflectionClass = new ReflectionClass(TestContextResolverClass::class);

    // Resolve the context
    $context = $resolver->execute($reflectionClass);

    // Create expected context
    $expectedContext = (new ContextFactory())->createFromReflector($reflectionClass);

    // Assertions
    expect($context)->toBeInstanceOf(Context::class);
    expect($context)->toEqual($expectedContext);
});

it('can resolve context from method', function () {
    $resolver = new ContextResolver();

    // Create a ReflectionMethod for the test class method
    $reflectionMethod = new ReflectionMethod(TestContextResolverClass::class, 'testMethod');

    // Resolve the context
    $context = $resolver->execute($reflectionMethod);

    // Create expected context
    $expectedContext = (new ContextFactory())->createFromReflector($reflectionMethod->getDeclaringClass());

    // Assertions
    expect($context)->toBeInstanceOf(Context::class);
    expect($context)->toEqual($expectedContext);
});

it('uses cache when resolving the same class multiple times', function () {
    $resolver = new ContextResolver();

    // Create a ReflectionClass for the test class
    $reflectionClass = new ReflectionClass(TestContextResolverClass::class);

    // Resolve the context the first time
    $context1 = $resolver->execute($reflectionClass);

    // Resolve the context the second time
    $context2 = $resolver->execute($reflectionClass);

    // Assertions
    expect($context1)->toBeInstanceOf(Context::class);
    expect($context2)->toBeInstanceOf(Context::class);
    expect($context1)->toBe($context2); // They should be the same instance, indicating the cache was used
});

// Test class
class TestContextResolverClass
{
    public $testProperty;

    public function testMethod()
    {
    }
}
