<?php

namespace Hbgl\Barcode\Tests;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /** @var array<\stdClass> */
    private static $cases = null;

    /**
     * @dataProvider is_cyclic_data_provider
     * @param string $key
     * @return void
     */
    public function test_is_cyclic_marker($key)
    {
        $case = self::$cases[$key];
        $array = null;
        if (isset($case->array)) {
            $array = &$case->array;
        } else {
            $array = ($case->factory)();
        }
        $actual = is_cyclic_marker($array, $case->depth);
        $this->assertEquals($case->cyclic, $actual);
    }

    /**
     * @dataProvider is_cyclic_data_provider
     * @param string $key
     * @return void
     */
    public function test_is_cyclic_count($key)
    {
        $case = self::$cases[$key];
        $array = null;
        if (isset($case->array)) {
            $array = &$case->array;
        } else {
            $array = ($case->factory)();
        }
        $actual = is_cyclic_count($array);
        $this->assertEquals($case->cyclic, $actual);
    }

    /**
     * @dataProvider is_cyclic_data_provider
     * @param string $key
     * @return void
     */
    public function test_is_cyclic_json_encode($key)
    {
        $case = self::$cases[$key];
        $array = null;
        if (isset($case->array)) {
            $array = &$case->array;
        } else {
            $array = ($case->factory)();
        }
        $actual = is_cyclic_json_encode($array);
        $this->assertEquals($case->cyclic, $actual);
    }

    /**
     * @return array<\stdClass>
     */
    public function is_cyclic_data_provider()
    {
        self::ensure_init_cases();
        $keys = array_keys(self::$cases);
        /** @var array<\stdClass> */
        $cases = array_combine($keys, array_map(function ($key) {
            return [$key];
        }, $keys));
        return $cases;
    }

    /**
     * @return void
     */
    private static function ensure_init_cases()
    {
        if (self::$cases !== null) {
            return;
        }

        $v = [1, 2, 3];
        $v[1] = &$v;
        self::$cases["References itself"] = (object)[
            'array' => &$v,
            'cyclic' => true,
            'depth' => 1,
        ];

        $x = [1, [2, 3]];
        $x[1][1] = &$x;
        self::$cases["Nested array references root"] = (object)[
            'array' => &$x,
            'cyclic' => true,
            'depth' => 2,
        ];

        $y = [1, [2, [3, 4]]];
        $y[1][1][1] = &$y[1];
        self::$cases["Nested array references ancestor"] = (object)[
            'array' => $y,
            'cyclic' => true,
            'depth' => 3,
        ];

        $z = [1, [2, 3]];
        $z[1][1] = &$z[1];
        self::$cases["Nested array with reference to itself"] = (object)[
            'array' => $z,
            'cyclic' => true,
            'depth' => 2,
        ];

        $p = function () {
            $array = [1, [2, 3]];
            $array[1][1] = &$array;
            return $array;
        };
        self::$cases["Bomb"] = (object)[
            'factory' => $p,
            'cyclic' => true,
            'depth' => 2,
        ];

        $r = [1, 2, [3, 4, [5, 6]]];
        $r[1] = $r;
        $r[2][1] = $r[2][2];
        self::$cases["Not cyclic"] = (object)[
            'array' => $r,
            'cyclic' => false,
            'depth' => 4,
        ];
    }
}
