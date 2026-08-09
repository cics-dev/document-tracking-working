<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::firstOrCreate(
    ['email' => 'celsovailoces@zppsu.edu.ph'],
    [
        'name' => 'Celsova Vailoces',
        'password' => Hash::make('password'),
        'position' => 'Administrator',
        'role_id' => 1,
        'office_id' => null,
    ]
);

echo $user->email . PHP_EOL;
