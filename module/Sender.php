<?php
namespace Phpushios;

use PhpushiosException;

class Sender
{
    /**
     * prod and dev environments
     */
    const ENVIRONMENTS = [
        'production' => 'https://api.push.apple.com',
        'development' => 'https://api.development.push.apple.com'
    ];

    /**
     * @var string generated authorization token
     */
    private $authToken;

    /**
     * @var array headers to be sent
     */
    protected $requestHeaders;

    /**
     * @var array user tokens
     */
    protected $receiversTokens = [];

    /**
     * @var string environment index
     */
    protected $environment;

    /**
     * @var string bundle id
     */
    protected $bundleId;

    /**
     * Sender constructor.
     *
     * @param string $environment
     * @param string $authToken
     * @param string $bundleId
     * @throws PhpushiosException
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
     * @param string $token
     * @throws PhpushiosException
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
     * @param string $token
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
     * @param resource $curl
     */
    private function setHeaders($curl)
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
     * @param string $payload
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
     * @param string $url
     * @param string $payload
     * @throws PhpushiosException
     */
    private function createHttp2Connection($url, $payload)
    {
        if (curl_version()['features'] & CURL_VERSION_HTTP2 !== 0) {

          $ch = curl_init();

            $this->setHeaders($ch);

            curl_setopt_array($ch,
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

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

             if ($response !== false && preg_match('~HTTP/2.0~', $response)) {

            } elseif ($response !== false) {
                 throw new PhpushiosException(
                     "No HTTP/2 support on server"
                 );
            } else {
                throw new PhpushiosException(
                    curl_error($ch)
                );
            }
            curl_close($ch);
        } else {
            throw new PhpushiosException(
                "No HTTP/2 support on client"
            );
        }
    }
}
