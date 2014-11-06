<?php


require "vendor/autoload.php";

var_dump(\Moontoast\Math\BigNumber::convertFromBase10(floor(microtime(true)*1000), 2));

$g = new \Aeon\IdGenerator();
for ($i=0; $i<100; $i++) {
    echo $g->generate() . "<br/>";
}