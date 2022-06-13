it('ca
n map using string', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'something' => 'We are the knights who say, ni!',
    ]);

    $this->assertEquals('We are the knights who say, ni!', $data->mapped);
});

it('can map in nested objects using strings', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('nested.something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'nested' => ['something' => 'We are the knights who say, ni!'],
    ]);

    $this->assertEquals('We are the knights who say, ni!', $data->mapped);
});

it('replaces properties when a mapped alternative exists', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'mapped' => 'We are the knights who say, ni!',
        'something' => 'Bring us a, shrubbery!',
    ]);

    $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
});

it('skips properties it cannot find', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'mapped' => 'We are the knights who say, ni!',
    ]);

    $this->assertEquals('We are the knights who say, ni!', $data->mapped);
});

it('can use integers to map properties', function () {
    $dataClass = new class () extends Data {
        #[MapInputName(1)]
        public string $mapped;
    };

    $data = $dataClass::from([
        'We are the knights who say, ni!',
        'Bring us a, shrubbery!',
    ]);

    $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
});

it('can use integers to map properties in nested data', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('1.0')]
        public string $mapped;
    };

    $data = $dataClass::from([
        ['We are the knights who say, ni!'],
        ['Bring us a, shrubbery!'],
    ]);

    $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
});

it('can combine integers and strings to map properties', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('lines.1')]
        public string $mapped;
    };

    $data = $dataClass::from([
        'lines' => [
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ],
    ]);

    $this->assertEquals('Bring us a, shrubbery!', $data->mapped);
});

it('can use a dedicated mapper', function () {
    $dataClass = new class () extends Data {
        #[MapInputName(SnakeCaseMapper::class)]
        public string $mappedLine;
    };

    $data = $dataClass::from([
        'mapped_line' => 'We are the knights who say, ni!',
    ]);

    $this->assertEquals('We are the knights who say, ni!', $data->mappedLine);
});

it('can map properties into data objects', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public SimpleData $mapped;
    };

    $value = collect([
        'something' => 'We are the knights who say, ni!',
    ]);

    $data = $dataClass::from($value);

    $this->assertEquals(SimpleData::from('We are the knights who say, ni!'), $data->mapped);
});

it('can map properties into data objects which map properties again', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something')]
        public SimpleDataWithMappedProperty $mapped;
    };

    $value = collect([
        'something' => [
            'description' => 'We are the knights who say, ni!',
        ],
    ]);

    $data = $dataClass::from($value);

    $this->assertEquals(
        new SimpleDataWithMappedProperty('We are the knights who say, ni!'),
        $data->mapped
    );
});

it('can map properties into data collections', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something'), DataCollectionOf(SimpleData::class)]
        public DataCollection $mapped;
    };

    $value = collect([
        'something' => [
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ],
    ]);

    $data = $dataClass::from($value);

    $this->assertEquals(
        SimpleData::collection([
            'We are the knights who say, ni!',
            'Bring us a, shrubbery!',
        ]),
        $data->mapped
    );
});

it('can map properties into data collections wich map properties again', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('something'), DataCollectionOf(SimpleDataWithMappedProperty::class)]
        public DataCollection $mapped;
    };

    $value = collect([
        'something' => [
            ['description' => 'We are the knights who say, ni!'],
            ['description' => 'Bring us a, shrubbery!'],
        ],
    ]);

    $data = $dataClass::from($value);

    $this->assertEquals(
        SimpleDataWithMappedProperty::collection([
            ['description' => 'We are the knights who say, ni!'],
            ['description' => 'Bring us a, shrubbery!'],
        ]),
        $data->mapped
    );
});

it('can map properties from a complete class', function () {
    $data = DataWithMapper::from([
        'cased_property' => 'We are the knights who say, ni!',
        'data_cased_property' =>
            ['string' => 'Bring us a, shrubbery!'],
        'data_collection_cased_property' => [
            ['string' => 'One that looks nice!'],
            ['string' => 'But not too expensive!'],
        ],
    ]);

    $this->assertEquals('We are the knights who say, ni!', $data->casedProperty);
    $this->assertEquals(SimpleData::from('Bring us a, shrubbery!'), $data->dataCasedProperty);
    $this->assertEquals(SimpleData::collection([
        'One that looks nice!',
        'But not too expensive!',
    ]), $data->dataCollectionCasedProperty);
});
