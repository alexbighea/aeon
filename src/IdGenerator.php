<?php

namespace Aeon;

use InvalidArgumentException;
use Zend\Math\BigInteger\Adapter\Bcmath;

class IdGenerator
{
    const MACHINE_ID_MAX = 63; //6 bits
    const SEQUENCE_MAX = 65535; //16 bits

    protected $machineId = 0;
    protected $lastSeq = 0;
    protected $epoque = 1324512000000; //42 years

    /** @var  \Zend\Math\BigInteger\Adapter\AdapterInterface */
    protected $math;

    private $machineIdBin = '000000';

    function __construct()
    {
        if (PHP_INT_SIZE == 4) {
            $this->math = new Bcmath();
        }
    }

    /**
     * @return int
     */
    public function getMachineId()
    {
        return $this->machineId;
    }

    /**
     * @param int $machineId
     */
    public function setMachineId($machineId)
    {
        if ($machineId < 0 || $machineId > self::MACHINE_ID_MAX) {
            throw new InvalidArgumentException(
                sprintf("Machine ID must be a 6 bit integer (max %s).", self::MACHINE_ID_MAX));
        }

        $this->machineId = $machineId;
        $this->machineIdBin = sprintf("%06b", $machineId);
    }

    public function generate()
    {
        if ($this->lastSeq > self::SEQUENCE_MAX) {
            $this->lastSeq = 0;
        }

        $time = floor(microtime(true) * 1000) - $this->epoque;

        if (PHP_INT_SIZE >= 8) {
            $time = (int)$time;

            return ($time << 22) | ($this->machineId << 16) | $this->lastSeq++;
        }

        $seqBin = sprintf("%016b", $this->lastSeq++);
        $timeBin = sprintf("%042s", $this->math->baseConvert($time, 10, 2));

        return $this->math->baseConvert($timeBin . $this->machineIdBin . $seqBin, 2);
    }
}