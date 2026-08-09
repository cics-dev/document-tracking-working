<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'celsovailoces@zppsu.edu.ph')->first();
$msg = (new App\Notifications\ResetPasswordNotification('abc123'))->toMail($user);

echo 'SUBJECT: ' . $msg->subject . PHP_EOL;
foreach ($msg->introLines as $line) {
    echo 'INTRO: ' . $line . PHP_EOL;
}
echo 'ACTION: ' . $msg->actionText . PHP_EOL;
foreach ($msg->outroLines as $line) {
    echo 'OUTRO: ' . $line . PHP_EOL;
}
echo 'SALUTATION: ' . $msg->salutation . PHP_EOL;
