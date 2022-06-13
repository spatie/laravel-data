it('ca
n create a data object with defaults empty', function () {
    $dataClass = new class ('', '', '') extends Data {
        public function __construct(
            public ?string $string,
            public Optional|string $optionalString,
            public string $stringWithDefault = 'Hi',
        ) {
        }
    };

    $this->assertEquals(
        new $dataClass(null, new Optional(), 'Hi'),
        $dataClass::from([])
    );
});
