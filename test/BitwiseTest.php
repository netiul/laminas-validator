<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Bitwise;
use PHPUnit\Framework\TestCase;

class BitwiseTest extends TestCase
{
    /**
     * @var \Laminas\Validator\Bitwise
     */
    public $validator;

    protected function setUp() : void
    {
        $this->validator = new Bitwise();
    }

    /**
     * @covers \Laminas\Validator\Bitwise::__construct()
     *
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $args, array $options): void
    {
        $validator = new Bitwise($args);

        $this->assertSame($options['control'], $validator->getControl());
        $this->assertSame($options['operator'], $validator->getOperator());
        $this->assertSame($options['strict'], $validator->getStrict());
    }
    /**
     * @covers \Laminas\Validator\Bitwise::__construct()
     *
     * @dataProvider constructDataProvider
     */
    public function testConstructWithTravesableOptions(array $args, array $options): void
    {
        $validator = new Bitwise(
            new \ArrayObject($args)
        );

        $this->assertSame($options['control'], $validator->getControl());
        $this->assertSame($options['operator'], $validator->getOperator());
        $this->assertSame($options['strict'], $validator->getStrict());
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: array,
     *     1: array<string, mixed>
     * }>
     */
    public function constructDataProvider(): array
    {
        return [
            [
                [],
                ['control' => null, 'operator' => null, 'strict' => false],
            ],
            [
                ['control' => 0x1],
                ['control' => 0x1, 'operator' => null, 'strict' => false],
            ],
            [
                ['control' => 0x1, 'operator' => Bitwise::OP_AND],
                ['control' => 0x1, 'operator' => Bitwise::OP_AND, 'strict' => false],
            ],
            [
                ['control' => 0x1, 'operator' => Bitwise::OP_AND, 'strict' => true],
                ['control' => 0x1, 'operator' => Bitwise::OP_AND, 'strict' => true],
            ],
        ];
    }

    /**
     * @covers \Laminas\Validator\Bitwise::isvalid()
     */
    public function testBitwiseAndNotStrict(): void
    {
        $controlSum = 0x7; // (0x1 | 0x2 | 0x4) === 0x7

        $validator = new Bitwise();
        $validator->setControl($controlSum);
        $validator->setOperator(Bitwise::OP_AND);

        $this->assertTrue($validator->isValid(0x1));
        $this->assertTrue($validator->isValid(0x2));
        $this->assertTrue($validator->isValid(0x4));
        $this->assertFalse($validator->isValid(0x8));

        $validator->isValid(0x8);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($validator::NOT_AND, $messages);
        $this->assertSame("The input has no common bit set with '$controlSum'", $messages[$validator::NOT_AND]);

        $this->assertTrue($validator->isValid(0x1 | 0x2));
        $this->assertTrue($validator->isValid(0x1 | 0x2 | 0x4));
        $this->assertTrue($validator->isValid(0x1 | 0x8));
    }

    /**
     * @covers \Laminas\Validator\Bitwise::isvalid()
     */
    public function testBitwiseAndStrict(): void
    {
        $controlSum = 0x7; // (0x1 | 0x2 | 0x4) === 0x7

        $validator = new Bitwise();
        $validator->setControl($controlSum);
        $validator->setOperator(Bitwise::OP_AND);
        $validator->setStrict(true);

        $this->assertTrue($validator->isValid(0x1));
        $this->assertTrue($validator->isValid(0x2));
        $this->assertTrue($validator->isValid(0x4));
        $this->assertFalse($validator->isValid(0x8));

        $validator->isValid(0x8);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($validator::NOT_AND_STRICT, $messages);
        $this->assertSame(
            "The input doesn't have the same bits set as '$controlSum'",
            $messages[$validator::NOT_AND_STRICT]
        );

        $this->assertTrue($validator->isValid(0x1 | 0x2));
        $this->assertTrue($validator->isValid(0x1 | 0x2 | 0x4));
        $this->assertFalse($validator->isValid(0x1 | 0x8));
    }

    /**
     * @covers \Laminas\Validator\Bitwise::isvalid()
     */
    public function testBitwiseXor(): void
    {
        $controlSum = 0x5; // (0x1 | 0x4) === 0x5

        $validator = new Bitwise();
        $validator->setControl($controlSum);
        $validator->setOperator(Bitwise::OP_XOR);

        $this->assertTrue($validator->isValid(0x2));
        $this->assertTrue($validator->isValid(0x8));
        $this->assertTrue($validator->isValid(0x10));
        $this->assertFalse($validator->isValid(0x1));
        $this->assertFalse($validator->isValid(0x4));

        $validator->isValid(0x4);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($validator::NOT_XOR, $messages);
        $this->assertSame("The input has common bit set with '$controlSum'", $messages[$validator::NOT_XOR]);

        $this->assertTrue($validator->isValid(0x8 | 0x10));
        $this->assertFalse($validator->isValid(0x1 | 0x4));
        $this->assertFalse($validator->isValid(0x1 | 0x8));
        $this->assertFalse($validator->isValid(0x4 | 0x8));
    }

    /**
     * @covers \Laminas\Validator\Bitwise::setOperator()
     */
    public function testSetOperator(): void
    {
        $validator = new Bitwise();

        $validator->setOperator(Bitwise::OP_AND);
        $this->assertSame(Bitwise::OP_AND, $validator->getOperator());

        $validator->setOperator(Bitwise::OP_XOR);
        $this->assertSame(Bitwise::OP_XOR, $validator->getOperator());
    }

    /**
     * @covers \Laminas\Validator\Bitwise::setStrict()
     */
    public function testSetStrict(): void
    {
        $validator = new Bitwise();

        $this->assertFalse($validator->getStrict(), 'Strict false by default');

        $validator->setStrict(false);
        $this->assertFalse($validator->getStrict());

        $validator->setStrict(true);
        $this->assertTrue($validator->getStrict());

        $validator = new Bitwise(0x1, Bitwise::OP_AND, false);
        $this->assertFalse($validator->getStrict());

        $validator = new Bitwise(0x1, Bitwise::OP_AND, true);
        $this->assertTrue($validator->getStrict());
    }

    public function testConstructorCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $control  = 0x1;
        $operator = Bitwise::OP_AND;
        $strict   = true;

        $validator = new Bitwise($control, $operator, $strict);

        $this->assertSame($control, $validator->getControl());
        $this->assertSame($operator, $validator->getOperator());
        $this->assertSame($strict, $validator->getStrict());
    }

    public function testCanRetrieveControlValue(): void
    {
        $control   = 0x1;
        $validator = new Bitwise($control, Bitwise::OP_AND, false);
        $this->assertSame($control, $validator->getControl());
    }

    public function testCanRetrieveOperatorValue(): void
    {
        $operator  = Bitwise::OP_AND;
        $validator = new Bitwise(0x1, $operator, false);
        $this->assertSame($operator, $validator->getOperator());
    }

    public function testCanRetrieveStrictValue(): void
    {
        $strict    = true;
        $validator = new Bitwise(0x1, Bitwise::OP_AND, $strict);
        $this->assertSame($strict, $validator->getStrict());
    }

    public function testIsValidReturnsFalseWithInvalidOperator(): void
    {
        $validator      = new Bitwise(0x1, 'or', false);
        $expectedResult = false;
        $this->assertEquals($expectedResult, $validator->isValid(0x2));
    }

    public function testCanSetControlValue(): void
    {
        $validator = new Bitwise();
        $control   = 0x2;
        $validator->setControl($control);
        $this->assertSame($control, $validator->getControl());
    }
}
