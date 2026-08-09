<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Password;

$status = Password::sendResetLink(['email' => 'celsovailoces@zppsu.edu.ph']);

echo $status . PHP_EOL;
