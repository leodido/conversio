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
    const CLASSNAME = 'Conversio\Conversion';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mock;

    public function setUp()
    {
        $this->mock = $this->getMockBuilder(self::CLASSNAME)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructor()
    {
        // array input
        $input = [];
        $this->mock->expects($this->at(0))
             ->method('setOptions')
             ->with($this->equalTo($input));

        $class = new \ReflectionClass(self::CLASSNAME);
        $ctor = $class->getConstructor();
        $ctor->invoke($this->mock, $input);

        // traversable input
        $input = new \ArrayIterator([]);
        $this->mock->expects($this->at(0))
            ->method('setOptions')
            ->with($this->equalTo($input->getArrayCopy()));

        $ctor->invoke($this->mock, $input);

        // string input
        $input = 'adapter';
        $this->mock->expects($this->at(0))
            ->method('setAdapter')
            ->with($this->equalTo($input));

        $ctor->invoke($this->mock, $input);

        // adapter input
        $input = $this->getMockForAbstractClass('Conversio\ConversionAlgorithmInterface');
        $this->mock->expects($this->at(0))
            ->method('setAdapter')
            ->with($this->equalTo($input));

        $ctor->invoke($this->mock, $input);

        // null input
        $input = null;
        $this->mock->expects($this->never())
            ->method('setAdapter')
            ->with($this->equalTo($input));
        $this->mock->expects($this->never())
            ->method('setOptions')
            ->with($this->equalTo($input));

        $ctor->invoke($this->mock, $input);
    }

    public function testGettingAdapterNotSetShouldThrowRuntimeException()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\RuntimeException');
        $filter->getAdapter();
    }

    public function testSettingInvalidTypeAdapterShouldThrowInvalidArgumentException()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\InvalidArgumentException');
        $filter->setAdapter(new \stdClass());
    }

    public function testSettingNonExistentAdapterShouldThrowRuntimeException()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\RuntimeException');
        $filter->setAdapter('Conversio\Phantom\NonExistentAdapter');

        $this->setExpectedException('Conversio\Exception\RuntimeException');
        $filter->getAdapter();
    }

    public function testSettingInvalidAdapterShouldThrowInvalidArgumentException()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\InvalidArgumentException');
        $filter->setAdapter('\ArrayIterator');

        $this->setExpectedException('Conversio\Exception\RuntimeException');
        $filter->getAdapter();
    }

    public function testSetAdapter()
    {
        $adapterClassName = 'ConversioTest\TestAsset\ConvertNothing';
        $filter = new Conversion();

        // string param
        $filter->setAdapter($adapterClassName);
        $this->assertInstanceOf($adapterClassName, $filter->getAdapter());
        $this->assertEquals($filter->getAdapter()->getName(), $filter->getAdapterName());

        // instance param
        /** @var $adapterInstance \ConversioTest\TestAsset\ConvertNothing */
        $adapterInstance = new $adapterClassName();
        $filter->setAdapter($adapterInstance);
        $this->assertInstanceOf($adapterClassName, $filter->getAdapter());
        $this->assertEquals($adapterInstance->getName(), $filter->getAdapterName());
    }

    public function testSettingInvalidOptionsShouldThrowInvalidArgumentException()
    {
        $filter = new Conversion();
        $this->setExpectedException('Conversio\Exception\InvalidArgumentException');
        $filter->setOptions('invalidoptions');
    }

    public function testSetOptions()
    {
        // NOTE: here we test the correct call sequence, not the integrity that is demanded to other test
        $onlyOpts = [
            'options' => ['prop1' => 1, 'prop2' => 2]
        ];

        $this->mock->expects($this->at(0))
            ->method('setAdapterOptions')
            ->with($this->equalTo($onlyOpts['options']));

        $class = new \ReflectionClass(self::CLASSNAME);
        $setOptsMethod = $class->getMethod('setOptions');
        $setOptsMethod->invoke($this->mock, $onlyOpts);

        $opts = [
            'adapter' => 'ConversioTest\TestAsset\ConvertNothing',
            'options' => ['prop1' => 1, 'prop2' => 2]
        ];

        $this->mock->expects($this->at(0))
            ->method('setAdapter')
            ->with($this->equalTo($opts['adapter']));
        $this->mock->expects($this->at(1))
            ->method('setAdapterOptions')
            ->with($this->equalTo($opts['options']));

        $class = new \ReflectionClass(self::CLASSNAME);
        $setOptsMethod = $class->getMethod('setOptions');
        $setOptsMethod->invoke($this->mock, $opts);
    }

    public function testSettingAdapterOptionsWithoutAdapterShouldThrowRuntimeException()
    {
        $onlyOpts = [
            'options' => ['prop1' => 1, 'prop2' => 2]
        ];
        $this->setExpectedException('Conversio\Exception\RuntimeException');
        new Conversion($onlyOpts);
    }
}
