<?php
/**
 * Conversio
 *
 * @link        https://github.com/leodido/conversio
 * @copyright   Copyright (c) 2014, Leo Di Donato
 * @license     http://opensource.org/licenses/ISC      ISC license
 */
namespace ConversioTest;

use Conversio\Conversion;

/**
 * Class ConversionTest
 */
class ConversionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $classname = 'Conversio\Conversion';

        $mock = $this->getMockBuilder($classname)
                     ->disableOriginalConstructor()
                     ->getMock();

        // array input
        $input = [];
        $mock->expects($this->at(0))
             ->method('setOptions')
             ->with($this->equalTo($input));

        $class = new \ReflectionClass($classname);
        $ctor = $class->getConstructor();
        $ctor->invoke($mock, $input);

        // traversable input
        $input = new \ArrayIterator([]);
        $mock->expects($this->at(0))
            ->method('setOptions')
            ->with($this->equalTo($input->getArrayCopy()));

        $ctor->invoke($mock, $input);

        // string input
        $input = 'adapter';
        $mock->expects($this->at(0))
            ->method('setAdapter')
            ->with($this->equalTo($input));

        $ctor->invoke($mock, $input);

        // adapter input
        $input = $this->getMockForAbstractClass('Conversio\ConversionAlgorithmInterface');
        $mock->expects($this->at(0))
            ->method('setAdapter')
            ->with($this->equalTo($input));

        $ctor->invoke($mock, $input);

        // null input
        $input = null;
        $mock->expects($this->never())
            ->method('setAdapter')
            ->with($this->equalTo($input));
        $mock->expects($this->never())
            ->method('setOptions')
            ->with($this->equalTo($input));

        $ctor->invoke($mock, $input);
    }

    public function testSetInvalidTypeAdapter()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\InvalidArgumentException');
        $filter->setAdapter(new \stdClass());
    }

    public function testSetNonExistentAdapter()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\RuntimeException');
        $filter->setAdapter('Conversio\Phantom\NonExistentAdapter');
    }

    public function testSetInvalidAdapter()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\InvalidArgumentException');
        $filter->setAdapter('\ArrayIterator');
    }

    public function testSetAdapter()
    {
        $adapterClassName = 'ConversioTest\TestAsset\ConvertNothing';
        $filter = new Conversion();

        // string param
        $filter->setAdapter($adapterClassName);
        $this->assertInstanceOf($adapterClassName, $filter->getAdapter());

        // instance param
        $adapterInstance = new $adapterClassName();
        $filter->setAdapter($adapterInstance);
        $this->assertInstanceOf($adapterClassName, $filter->getAdapter());
    }
}
