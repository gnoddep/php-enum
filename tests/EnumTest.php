<?php

namespace Frank\Test;

use Frank\Enum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class EnumTest extends TestCase
{
    public function testValidConstruction()
    {
        $this->assertEquals(2, (new _EnumTest(2))->value());
    }

    public function testInvalidConstruction()
    {
        $this->expectException(InvalidArgumentException::class);
        new _EnumTest(10);
    }

    public function testGetAll()
    {
        $all = _EnumTest::all();
        $this->assertTrue($all['HELLO']->is(5));
        $this->assertTrue($all['NOT_TRUE']->is(false));
    }

    public function testAssertEquals()
    {
        (new _EnumTest(2))->assertEquals(_EnumTest::BAR());
        $this->assertTrue(true);
    }

    public function testAssertEqualsWhenInstanceOf()
    {
        (new _EnumTest(2))->assertEquals(
            new class(2) extends _EnumTest {
                const BAZ = 'baz';
            }
        );

        $this->assertTrue(true);
    }

    /**
     * @param mixed $other
     * @dataProvider notEqualsProvider
     */
    public function testAssertEqualsFailsWhenNotEqual($other)
    {
        $this->expectException(UnexpectedValueException::class);
        (new _EnumTest(2))->assertEquals($other);
    }

    public function notEqualsProvider()
    {
        return [
            [_EnumTest::FOO()],
            [
                new class('baz') extends _EnumTest {
                    const BAZ = 'baz';
                }
            ],
        ];
    }

    /**
     * @param mixed $other
     * @dataProvider notInstanceOfProvider
     */
    public function testAssertEqualsFailsWhenNotInstanceOf($other)
    {
        $this->expectException(InvalidArgumentException::class);
        (new _EnumTest(2))->assertEquals($other);
    }

    public function notInstanceOfProvider()
    {
        return [
            [null],
            [false],
            [1],
            [3.14],
            ['string'],
            [new class() {}],
            [
                new class(1) extends Enum
                {
                    const FOO = 1;
                }
            ],
        ];
    }

    /**
     * @param $value
     * @dataProvider validValues
     */
    public function testForValidValues($value)
    {
        $this->assertTrue(_EnumTest::isValidValue($value));
    }

    public function validValues(): array
    {
        return [[1], [2], [5], [7], ['placed'], ['yolo'], [false]];
    }

    /**
     * @param $value
     * @dataProvider invalidValues
     */
    public function testForInvalidValues($value)
    {
        $this->assertFalse(_EnumTest::isValidValue($value));
    }

    public function invalidValues(): array
    {
        return [[-1], [0], [4], [10], ['ape'], ['BANANAS'], [true], [[1, 2, 3]]];
    }

    public function testItGivesRightConstants()
    {
        $expected = [
            'FOO' => 1,
            'BAR' => 2,
            'HELLO' => 5,
            'BYE' => 7,
            'PLACED' => 'placed',
            'YOLO' => 'yolo',
            'NOT_TRUE' => false,
            'NONE' => null,
        ];

        $this->assertEquals($expected, _EnumTest::getConstants());
    }

    public function testToString()
    {
        $this->assertEquals('1', _EnumTest::FOO()->__toString());
        $this->assertEquals((string)false, _EnumTest::NOT_TRUE()->__toString());
    }

    public function testStaticCallsProduceTheSameObject()
    {
        $this->assertSame(_EnumTest::FOO(), _EnumTest::FOO());
    }

    public function testOfProduceTheSameObjectAsStaticCalls()
    {
        $this->assertSame(_EnumTest::of(_EnumTest::FOO), _EnumTest::FOO());
    }

    public function testInvalidStaticCallsProduceAnException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DOESNOTEXIST does not exist in ' . _EnumTest::class);

        _EnumTest::DOESNOTEXIST();
    }

    public function testNullValueDoesNotProduceAnException()
    {
        $this->assertNull(_EnumTest::NONE()->value());
    }

    public function testMemoryUsage()
    {
        $iterations = 1000;
        $list = range(0, $iterations);

        $start = memory_get_usage();
        foreach ($list as $ii) {
            $list[$ii] = _EnumTest::FOO();
        }
        $end = memory_get_usage();

        $this->assertEquals($start, $end);
    }

    public function testIsAny()
    {
        $this->assertTrue(_EnumTest::FOO()->isAny(['dsdf', null, '1', _EnumTest::FOO, true]));
        $this->assertFalse(_EnumTest::FOO()->isAny(['dsdf', null, '1', true]));
    }

    public function testEqualsAny()
    {
        $this->assertTrue(_EnumTest::FOO()->equalsAny(_EnumTest::all()));
        $this->assertFalse(_EnumTest::FOO()->equalsAny([_EnumTest::NONE(), _EnumTest::BAR(), new class() {}]));
    }

    public function testOfList()
    {
        $this->assertEquals([_EnumTest::FOO(), _EnumTest::BAR()], _EnumTest::ofList([_EnumTest::FOO, _EnumTest::BAR]));
    }

    public function testDontAllowEnumCreationWithPrivateConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('private is not a valid value for Frank\Test\_EnumTest');
        new _EnumTest('private');
    }

    public function testDontExposePrivateConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('I_AM_PRIVATE does not exist in Frank\Test\_EnumTest');
        _EnumTest::I_AM_PRIVATE();
    }

    public function testDontAllowEnumCreationWithProtectedConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('protected is not a valid value for Frank\Test\_EnumTest');
        new _EnumTest('protected');
    }

    public function testDontExposeProtectedConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('I_AM_PROTECTED does not exist in Frank\Test\_EnumTest');
        _EnumTest::I_AM_PROTECTED();
    }
}

/**
 * @method static _EnumTest FOO()
 * @method static _EnumTest BAR()
 * @method static _EnumTest NOT_TRUE()
 * @method static _EnumTest NONE()
 */
class _EnumTest extends Enum
{
    const FOO = 1;
    const BAR = 2;
    const HELLO = 5;
    const BYE = 7;
    const PLACED = 'placed';
    const YOLO = 'yolo';
    const NOT_TRUE = false;
    const NONE = null;

    private const I_AM_PRIVATE = 'private';
    protected const I_AM_PROTECTED = 'protected';
}
