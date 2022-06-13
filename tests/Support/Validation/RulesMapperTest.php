it('ca
uses(TestCase::class);
n map string rules', function () {
    $this->assertEquals(
        [new Required()],
        $this->mapper->execute(['required'])
    );
});

it('can map string rules with arguments', function () {
    $this->assertEquals(
        [new Exists(rule: new BaseExists('users'))],
        $this->mapper->execute(['exists:users'])
    );
});

it('can map string rules with key value arguments', function () {
    $this->assertEquals(
        [new Dimensions(minWidth: 100, minHeight: 200)],
        $this->mapper->execute(['dimensions:min_width=100,min_height=200'])
    );
});

it('can map multiple rules', function () {
    $this->assertEquals(
        [new Required(), new Min(0)],
        $this->mapper->execute(['required', 'min:0'])
    );
});

it('can map multiple concatenated rules', function () {
    $this->assertEquals(
        [new Required(), new Min(0)],
        $this->mapper->execute(['required|min:0'])
    );
});

it('can map faulty rules', function () {
    $this->assertEquals(
        [new Rule('min:')],
        $this->mapper->execute(['min:'])
    );
});

it('can map laravel rule objects', function () {
    $this->assertEquals(
        [new Exists('users')],
        $this->mapper->execute([new BaseExists('users')])
    );
});

it('can map custom laravel rule objects', function () {
    $rule = new class () implements CustomRuleContract {
        public function passes($attribute, $value) {
        }

        public function message() {
        }
    };

    $this->assertEquals(
        [new Rule($rule)],
        $this->mapper->execute([$rule])
    );
});
