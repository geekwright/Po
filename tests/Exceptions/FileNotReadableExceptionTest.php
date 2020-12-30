<?php
namespace Geekwright\Po\Exceptions;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-02-18 at 00:42:27.
 */
class FileNotReadableExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileNotReadableException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        $this->object = new FileNotReadableException;
    }

    public function testContracts()
    {
        $this->assertInstanceOf('\Geekwright\Po\Exceptions\FileNotReadableException', $this->object);
        $this->assertInstanceOf('\RuntimeException', $this->object);
    }

    public function testException()
    {
        $this->expectException('\Geekwright\Po\Exceptions\FileNotReadableException');
        throw $this->object;
    }
}
