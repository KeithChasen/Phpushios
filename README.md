Phpushious
=
Simple php library for iOS push notifications with token based authentication and HTTP/2

Requirements
=
+ PHP >= 7.0.12
+ curl >= 7.46.0
+ openssl >= 1.0.2

Simplest using example
-
```php
<?php

use Phpushios\Auth;
use Phpushios\Message;
use Phpushios\Sender;

$apnsKeyId = 'NHDF3G4HS3';

$apnsAuthKey = 'path/to/APNSAuthKey_NHDF3G4HS3.p8';

$teamId = 'CH63KE89LH';

$secret = null;

$bundleId = 'com.somebundle.id';

$env = 'production';

$userToken = 'ae01912dee52f0dc41b16a2fa9d68ff631dc9112b7d629f24008f466e1efef2d';

$auth = new Auth($apnsAuthKey);
$authToken = $auth->setAuthToken($apnsKeyId, $secret, $teamId);
$pushSender = new Sender($env, $authToken, $bundleId);
$message = new Message();

$messageToSend = 'push message with p8';

$pushSender->addReceiver($userToken);
$message->setAlert($messageToSend);
$payload = $message->setPayload();
$pushSender->sendPush($payload);
```

Example using custom badge number for particular device token
-
```php
<?php

use Phpushios\Auth;
use Phpushios\Message;
use Phpushios\Sender;

$apnsKeyId = 'NHDF3G4HS3';

$apnsAuthKey = 'path/to/APNSAuthKey_NHDF3G4HS3.p8';

$teamId = 'CH63KE89LH';

$secret = null;

$bundleId = 'com.somebundle.id';

$env = 'development';

$tokens = [
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7a', 'badge' => 12],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7b', 'badge' => 32],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7c', 'badge' => 42],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7d', 'badge' => 55],
    ['token' => 'a3r1n3c8596f56f39921f79c55c91a061ea5042ab7be00620e7df0c76069aa7e', 'badge' => 11],
];

$auth = new Auth($apnsAuthKey);
$authToken = $auth->setAuthToken($apnsKeyId, $secret, $teamId);
$pushSender = new Sender($env, $authToken, $bundleId);
$message = new Message();

$messageToSend = 'push message with p8';

foreach ($tokens as $token) {
    $pushSender->addReceiver($token['token']);
    $message->setAlert($messageToSend);
    $message->setBadgeNumber($token['badge']);
    $payload = $message->setPayload();
    $pushSender->sendPush($payload);
    $pushSender->removeReceiversToken($token['token']);
}
```