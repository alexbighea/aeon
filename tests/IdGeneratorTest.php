<?php

use Aeon\IdGenerator;

class DatabaseLogTest extends PHPUnit_Framework_TestCase
{
    /** @var  IdGenerator */
    protected $idgen;

    protected function setUp()
    {
        $this->idgen = new IdGenerator();
    }

    public function testUnique()
    {
        $ids = [];

        for($i = 0; $i < 10; $i++) {
            $ids[] = $this->idgen->generate();
        }

        $unique = array_unique($ids);
        $this->assertEquals($unique, $ids);
        sort($ids, SORT_NATURAL);
        $this->assertEquals($unique, $ids);
    }

    public function testMakeAndSplit()
    {
        $parts = [
            'time' => (string)floor(microtime(true) * 1000),
            'machineId' => mt_rand(0, IdGenerator::MACHINE_ID_MAX),
            'seq' => mt_rand(0, IdGenerator::SEQUENCE_MAX)
        ];

        $id = $this->idgen->make($parts['time'], $parts['machineId'], $parts['seq']);

        $split = $this->idgen->split($id);

        $this->assertEquals($parts, $split);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetMachineIdOutOfRange()
    {
        $this->idgen->setMachineId(IdGenerator::MACHINE_ID_MAX+1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMakeMachineIdOutOfRange()
    {
        $this->idgen->make(floor(microtime(true) * 1000), IdGenerator::MACHINE_ID_MAX+1, 0);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMakeSequenceOutOfRange()
    {
        $this->idgen->make(floor(microtime(true) * 1000), 0, IdGenerator::SEQUENCE_MAX+1);
    }
}
