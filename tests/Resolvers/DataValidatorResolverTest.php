it('ca
n set the validator to stop on the first failure', function () {
    $dataClass = new class () extends Data {
        public static function stopOnFirstFailure(): bool
        {
            return true;
        }
    };

    $validator = $this->resolver->execute($dataClass::class, []);

    $this->assertTrue(invade($validator)->stopOnFirstFailure);
});
