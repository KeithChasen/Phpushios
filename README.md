IOS PHP Pusher
=
Simple ios-php push library with token based authentication and HTTP/2

Requirements
=
+ PHP >= 7.0.12
+ curl >= 7.46.0
+ openssl >= 1.0.2

Example using only alert
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

$auth = new \Module\Auth($apnsAuthKey);
$authToken = $auth->setAuthToken($apnsKeyId, $secret, $teamId);
$pushSender = new \Module\Sender($env, $authToken, $bundleId);
$message = new \Module\Message();

$messageToSend = 'test push p8';

$pushSender->addReceiver($userToken);
$message->setAlert($messageToSend);
$payload = $message->setPayload();
$pushSender->sendPush($payload);
```

Example using custom badge number for particular token
-
```php
<?php

$apnsKeyId = 'NHDF3G4HS3';

$apnsAuthKey = 'path/to/APNSAuthKey_NHDF3G4HS3.p8';

$teamId = 'CH63KE89LH';

$secret = null;

$bundleId = 'com.somebundle.id';

$env = 0;

$tokens = [
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7a', 'badge' => 12],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7b', 'badge' => 32],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7c', 'badge' => 42],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7d', 'badge' => 55],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7e', 'badge' => 11],
];

$auth = new \Module\Auth($apnsAuthKey);
$authToken = $auth->setAuthToken($apnsKeyId, $secret, $teamId);
$pushSender = new \Module\Sender($env, $authToken, $bundleId);
$message = new \Module\Message();

$messageToSend = 'test push p8';

foreach ($tokens as $token) {
    $pushSender->addReceiver($token['token']);
    $message->setAlert($messageToSend);
    $message->setBadgeNumber($token['badge']);
    $payload = $message->setPayload();
    $pushSender->sendPush($payload);
    $pushSender->removeReceiversToken($token['token']);
}
```