<?php

// .\vendor\bin\phpbench run bench.php --report=generator:""table"",cols:[""subject"",""set"",""mean"",""diff""],sort:{set:""asc"",subject:""asc""},break:[""set""] --warmup=5 --revs=10

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

/**
 * @BeforeMethods({"init"})
 */
class IsCyclicBench
{
    /** @var array<array<mixed>> */
    private static $arrays = null;

    /**
     * @ParamProviders({"provide_arrays"})
     * @param array<string> $args
     * @return bool
     */
    public function bench_marker($args)
    {
        $array = &self::$arrays[$args[0]];
        $is_cyclic = is_cyclic_marker($array);
        return $is_cyclic;
    }

    /**
     * @ParamProviders({"provide_arrays"})
     * @param array<string> $args
     * @return bool
     */
    public function bench_count($args)
    {
        $array = &self::$arrays[$args[0]];
        $is_cyclic = is_cyclic_count($array);
        return $is_cyclic;
    }

    /**
     * @ParamProviders({"provide_arrays"})
     * @param array<string> $args
     * @return bool
     */
    public function bench_json_encode($args)
    {
        $array = &self::$arrays[$args[0]];
        $is_cyclic = is_cyclic_json_encode($array);
        return $is_cyclic;
    }

    /**
     * @return void
     */
    public function init()
    {
        if (self::$arrays === null) {
            // Very small
            $very_small_cyclic = [1, 2, 3];
            $very_small_cyclic[1] = &$very_small_cyclic;
            self::$arrays['00 Very small cyclic'] = &$very_small_cyclic;

            $very_small_noncyclic = [1, 2, 3];
            self::$arrays['01 Very small noncyclic'] = &$very_small_noncyclic;

            // Small
            $small_cyclic = range(1, 20);
            $small_cyclic[10] = range(1, 20);
            $small_cyclic[10][10] = &$small_cyclic[10];
            self::$arrays['02 Small cyclic'] = &$very_small_cyclic;

            $small_noncyclic = range(1, 20);
            $small_noncyclic[10] = range(1, 20);
            self::$arrays['03 Small noncyclic'] = &$small_noncyclic;

            // Medium
            $medium_cyclic = range(1, 500);
            $medium_cyclic[100] = range(1, 500);
            $medium_cyclic[100][200] = range(1, 500); /** @phpstan-ignore-line */
            $medium_cyclic[100][200][300] = &$medium_cyclic;
            self::$arrays['04 Medium cyclic'] = &$medium_cyclic;

            $medium_noncyclic = range(1, 500);
            $medium_noncyclic[100] = range(1, 500);
            $medium_noncyclic[100][200] = range(1, 500); /** @phpstan-ignore-line */
            self::$arrays['05 Medium noncyclic'] = &$medium_noncyclic;

            // Large
            $large_cyclic = range(1, 10000);
            $large_cyclic[1000] = range(1, 10000);
            $large_cyclic[1000][2000] = range(1, 10000); /** @phpstan-ignore-line */
            $large_cyclic[1000][2000][3000] = range(1, 10000);
            $large_cyclic[1000][2000][3000][4000] = &$large_cyclic;
            self::$arrays['06 Large cyclic'] = &$large_cyclic;

            $large_noncyclic = range(1, 10000);
            $large_noncyclic[1000] = range(1, 10000);
            $large_noncyclic[1000][2000] = range(1, 10000); /** @phpstan-ignore-line */
            $large_noncyclic[1000][2000][3000] = range(1, 10000);
            self::$arrays['07 Large noncyclic'] = &$large_noncyclic;

            // Very large
            $gen_str = function () {
                return str_repeat('abcdef', 200);
            };

            $very_large_cyclic = array_map($gen_str, range(1, 10000));
            $very_large_cyclic[1000] = array_map($gen_str, range(1, 10000));
            $very_large_cyclic[1000][2000] = array_map($gen_str, range(1, 10000));
            $very_large_cyclic[1000][2000][3000] = array_map($gen_str, range(1, 10000));
            $very_large_cyclic[1000][2000][3000][4000] = &$very_large_cyclic;
            self::$arrays['08 Very large cyclic'] = &$very_large_cyclic;

            $very_large_noncyclic = array_map($gen_str, range(1, 10000));
            $very_large_noncyclic[1000] = array_map($gen_str, range(1, 10000));
            $very_large_noncyclic[1000][2000] = array_map($gen_str, range(1, 10000));
            $very_large_noncyclic[1000][2000][3000] = array_map($gen_str, range(1, 10000));
            self::$arrays['09 Very large noncyclic'] = &$very_large_noncyclic;
        }
    }

    /**
     * @return array<string>
     */
    public function provide_arrays()
    {
        $keys = [
            '00 Very small cyclic',
            '01 Very small noncyclic',
            '02 Small cyclic',
            '03 Small noncyclic',
            '04 Medium cyclic',
            '05 Medium noncyclic',
            '06 Large cyclic',
            '07 Large noncyclic',
            '08 Very large cyclic',
            '09 Very large noncyclic',
        ];
        $data = array_map(function ($key) {
            return [$key];
        }, array_combine($keys, $keys));
        return $data;
    }
}
