<?php

namespace Spatie\LaravelData\Tests\Benchmarks;

// Required imports for Data classes and Test
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\Attributes\Validation\Max; // For BenchmarkIdData
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\TestCase;

// --- Data Class Definitions ---

class BenchmarkIdData extends Data
{
    public function __construct(
        public int $id,
        #[Max(100)]
        public string $name,
    ) {
    }
}

class BenchmarkArrayData extends Data
{
    /** @param BenchmarkIdData[] $array */
    public function __construct(
        public array $array,
    ) {
    }
}

// --- Test Class Definition ---

class SimpleValidationBenchmarkTest extends TestCase
{
    #[Test]
    public function simple_benchmark_performance_comparison()
    {
        $count = 5000;
        $array = array_map(fn (int $id) => [
            'id' => $id,
            'name' => 'Item Name ' . $id // Add name field
        ], range(1, $count));
        $payload = ['array' => $array];

        // --- Manual Timing ---
        $runs = 3;
        $nativeTimes = [];
        $dataTimes = [];

        // Updated native rules
        $nativeRules = [
            'array' => 'required|array',
            'array.*.id' => 'required|integer',
            'array.*.name' => 'required|string|max:100', // Add rules for name
        ];

        for ($i = 0; $i < $runs; $i++) {
            $start = microtime(true);
            Validator::validate($payload, $nativeRules);
            $nativeTimes[] = microtime(true) - $start;

            $start = microtime(true);
            BenchmarkArrayData::validate($payload);
            $dataTimes[] = microtime(true) - $start;
        }

        $nativeAvg = array_sum($nativeTimes) / $runs;
        $dataAvg = array_sum($dataTimes) / $runs;
        // --- End Manual Timing ---

        // Output the results (will appear during test execution)
        $nativeMs = round($nativeAvg * 1000, 3);
        $dataMs = round($dataAvg * 1000, 3);

        echo "\n--- Simple Benchmark Results ---\n";
        echo "Native Laravel Validator (avg of {$runs} runs): {$nativeMs} ms\n";
        echo "Spatie Laravel Data (avg of {$runs} runs):    {$dataMs} ms\n";
        echo "----------------------------------\n";

        $this->assertTrue(true);
    }
}
