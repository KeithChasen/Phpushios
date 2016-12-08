<?php

/**
 * Library for sending iOS push notifications using p8 certificate
 *
 * PHP version 7
 *
 * @category Sending
 * @package  Phpushios
 * @author   Keith Chasen <keithchasen89@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  GIT: $Id$
 * @link     https://github.com/KeithChasen/Phpushios
 */

namespace Phpushios;

use PhpushiosException;

/**
 * Performing sending push notification
 *
 * @category Sending
 * @package  Phpushios
 * @author   Keith Chasen <keithchasen89@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  Release: 1.0.0
 * @link     https://github.com/KeithChasen/Phpushios
 */
class Sender
{
    /**
     * Production and development environments
     */
    const ENVIRONMENTS = [
        'production' => 'https://api.push.apple.com',
        'development' => 'https://api.development.push.apple.com'
    ];

    /**
     * Generated authorization token
     *
     * @var string
     */
    protected $authToken;

    /**
     * Headers to be sent
     *
     * @var array
     */
    protected $requestHeaders;

    /**
     * User tokens
     *
     * @var array
     */
    protected $receiversTokens = [];

    /**
     * Environment name
     *
     * @var string
     */
    protected $environment;

    /**
     * Bundle id
     *
     * @var string
     */
    protected $bundleId;

    /**
     * Sender constructor.
     *
     * @param string $environment Environment value to send push to
     *                            Options: 'development', 'production'
     * @param string $authToken   Generated authorization token encoded
     *                            with ES256 algorithm
     * @param string $bundleId    Bundle Id value
     *
     * @throws PhpushiosException Invalid environment value was used
     * @throws PhpushiosException Empty authorization token was used
     */
    public function __construct($environment, $authToken, $bundleId)
    {
        if (!array_key_exists($environment, self::ENVIRONMENTS)) {
            throw new PhpushiosException(
                'Invalid environment ' . $environment
            );
        }
        $this->environment = $environment;

        if (empty($authToken)) {
            throw new PhpushiosException(
                'Empty auth token'
            );
        }

        $this->authToken = $authToken;
        $this->bundleId = $bundleId;
    }

    /**
     * Add receivers token
     *
     * @param string $token Device token which should accept push notification
     *
     * @throws PhpushiosException Invalid token was used
     *
     * @return void
     */
    public function addReceiver($token)
    {
        if (!preg_match('~[a-f0-9]{64}~', $token)) {
            throw new PhpushiosException(
                "Invalid token " . $token
            );
        }
        $this->receiversTokens[] = $token;
    }

    /**
     * Finds device token and removes it from receiversTokens array
     *
     * @param string $token Device token to be deleted from receivers tokens
     *
     * @return void
     */
    public function removeReceiversToken($token)
    {
        if (in_array($token, $this->receiversTokens)) {
            unset(
                $this->receiversTokens[
                    array_search(
                        $token,
                        $this->receiversTokens
                    )
                ]
            );
        }
    }

    /**
     * Sets headers to be sent with push notification
     *
     * @param resource $curl Curl connection instance
     *
     * @return void
     */
    protected function setHeaders($curl)
    {
        $this->requestHeaders = [
            'apns-expiration: 0',
            'apns-priority: 10',
            'apns-topic: ' . $this->bundleId,
            'authorization: bearer ' . $this->authToken
        ];

        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->requestHeaders);
    }

    /**
     * Sends push notification to every device from receiversTokens array
     *
     * @param string $payload Json encoded payload to be sent
     *
     * @return void
     */
    public function sendPush($payload)
    {
        foreach ($this->receiversTokens as $receiversToken) {

            $tokenPartUrl = '/3/device/' . $receiversToken;

            $baseUrl = self::ENVIRONMENTS[$this->environment];

            $urlToSend = $baseUrl . $tokenPartUrl;

            $this->createHttp2Connection($urlToSend, $payload);
        }
    }

    /**
     * Creates and executes
     *
     * @param string $url     APNS url to be used
     * @param string $payload Payload to be sent
     *
     * @throws PhpushiosException HTTP/2 not supported on server
     * @throws PhpushiosException HTTP/2 not supported on client
     * @throws PhpushiosException Empty response
     *
     * @return void
     */
    protected function createHttp2Connection($url, $payload)
    {
        if (curl_version()['features'] & CURL_VERSION_HTTP2 !== 0) {

            $curlResource = curl_init();

            $this->setHeaders($curlResource);

            curl_setopt_array(
                $curlResource,
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                    CURLOPT_URL => $url,
                    CURLOPT_PORT => 443,
                    CURLOPT_HEADER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30
                ]
            );

            $response = curl_exec($curlResource);
            $httpcode = curl_getinfo($curlResource, CURLINFO_HTTP_CODE);

            if (false !== $response
                && preg_match('~HTTP/2.0~', $response)
            ) {

            } elseif ($response !== false) {
                 throw new PhpushiosException(
                     "No HTTP/2 support on server"
                 );
            } else {
                throw new PhpushiosException(
                    curl_error($curlResource)
                );
            }
            curl_close($curlResource);
        } else {
            throw new PhpushiosException(
                "No HTTP/2 support on client"
            );
        }
    }
}
