<?php

use App\Kernel;

$kernel = new Kernel('tests', false);
$kernel->boot();

return $kernel->getContainer();