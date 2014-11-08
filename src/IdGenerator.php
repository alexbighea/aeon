<?php

namespace Aeon;

use InvalidArgumentException;

class IdGenerator
{
    const MACHINE_ID_MAX = 63; //6 bits
    const SEQUENCE_MAX = 65535; //16 bits

    protected $machineId = 0;
    protected $seq = 0;
    protected $epoque = 1324512000000; //42 years

    function __construct()
    {
        bcscale(0);
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

        $this->machineId = (int)$machineId;
    }

    public function generate()
    {
        if ($this->seq > self::SEQUENCE_MAX) {
            $this->seq = 0;
        }

        $time = floor(microtime(true) * 1000) - $this->epoque;

        return $this->make($time, $this->machineId, $this->seq++);
    }

    /**
     * @param int|string $time Time in milliseconds
     * @param int $machineId
     * @param int $seq
     * @return int|string
     */
    public function make($time, $machineId, $seq)
    {
        if ($machineId < 0 || $machineId > self::MACHINE_ID_MAX) {
            throw new InvalidArgumentException(
                sprintf("Machine ID must be a 6 bit integer (max %s).", self::MACHINE_ID_MAX));
        }

        if ($seq < 0 || $seq > self::SEQUENCE_MAX) {
            throw new InvalidArgumentException(
                sprintf("Sequence must be a 16 bit integer (max %s).", self::SEQUENCE_MAX));
        }

        if (PHP_INT_SIZE >= 8) {
            return ((int)$time << 22) | ((int)$machineId << 16) | (int)$seq;
        }

        $timeBin = sprintf("%042s", $this->bigInt2bin($time));
        $machineIdBin = sprintf("%06b", $machineId);
        $seqBin = sprintf("%016b", $seq);

        return $this->bin2bigInt($timeBin . $machineIdBin . $seqBin);
    }

    /**
     * @param int|string $id
     * @return array
     */
    public function split($id)
    {
        if (PHP_INT_SIZE >= 8) {
            return [
                'time' => $id >> 22,
                'machineId' => ($id >> 16) & bindec('111111'),
                'seq' => $id & bindec('1111111111111111')
            ];
        }

        $idBin = $this->bigInt2bin($id);

        return [
            'time' => $this->bin2bigInt(substr($idBin, 0, -22)),
            'machineId' => bindec(substr($idBin, -22, 6)),
            'seq' => bindec(substr($idBin, -16))
        ];
    }

    private function bin2bigInt($bin)
    {
        $dec = '0';

        for ($i = 0; $i < strlen($bin); $i++) {
            $dec = bcmul($dec, '2');
            $dec = bcadd($dec, $bin[$i]);
        }

        return($dec);
    }

    private function bigInt2bin($dec)
    {
        $bin = '';

        do {
            $bin = bcmod($dec, '2') . $bin;
            $dec = bcdiv($dec, '2');
        } while (bccomp($dec, '0'));

        return($bin);
    }
}