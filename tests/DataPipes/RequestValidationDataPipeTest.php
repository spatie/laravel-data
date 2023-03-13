<?php

it('use request validationData', function () {
    $dataClass = new class () extends \Spatie\LaravelData\Data {
        public int $id;
        public string $token;

        public static function validationData(\Illuminate\Http\Request $request): array
        {
            return ['id' => 1, 'token' => $request->header('token')];
        }
    };

    $request = Mockery::mock(\Illuminate\Http\Request::class);
    $request->expects('header')->with('token')->andReturn('hello');
    $request->expects('toArray')->andReturn([]);

    $data = $dataClass::from($request);

    expect($data->id)->toEqual(1);
    expect($data->token)->toEqual('hello');
});
