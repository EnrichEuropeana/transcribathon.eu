<?php


$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Psr\\Container\\' => array($vendorDir . '/psr/container/src'),
    'AcyMailing\\' => array($baseDir . '/'),
);
