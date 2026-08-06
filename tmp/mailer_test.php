<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

Mail::raw('SMTP verification from DTS-ZPPSU', function ($message) {
    $message->from('celzvailoces@gmail.com', 'DTS-ZPPSU')
        ->to('celzvailoces@gmail.com')
        ->subject('DTS-ZPPSU SMTP Verification');
});

echo "MAIL_SENT_TEST\n";
