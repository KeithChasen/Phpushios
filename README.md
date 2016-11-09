IOS PHP Pusher
=
Simple ios-php push library with token based authentication and HTTP/2

Example
-
```php
<?php

$apnsKeyId = 'NHDF3G4HS3';

$apnsAuthKey = 'path/to/APNSAuthKey_NHDF3G4HS3.p8';

$teamId = 'CH63KE89LH';

$secret = null;

$bundleId = 'com.somebundle.id';

$env = 0;

$userToken = 'ae01912dee52f0dc41b16a2fa9d68ff631dc9112b7d629f24008f466e1efef2d';

$pushSender = new \Module\Sender(
    $env,
    $apnsAuthKey,
    $apnsKeyId,
    $teamId,
    $bundleId,
    $secret
);

$pushSender->addReceiver($userToken);
$pushSender->sendPush('Push with p8');
?>
```
