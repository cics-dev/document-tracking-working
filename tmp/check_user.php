<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'celsovailoces@zppsu.edu.ph')->first();

if ($user) {
    echo $user->email . PHP_EOL;
} else {
    echo 'NO_USER' . PHP_EOL;
}
