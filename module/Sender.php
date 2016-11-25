<?php

namespace Module;



class Sender
{
    /**
     * 0 = > prod
     * 1 = > dev
     */
    const ENVIRONMENTS = [
        'https://api.push.apple.com',
        'https://api.development.push.apple.com'
    ];

    /**
     * @var $token
     * auth token
     */
    private $authToken;

    /**
     * @var $request_headers
     */
    protected $requestHeaders;

    /**
     * @var array
     * array of user tokens
     */
    protected $receiversTokens = [];

    /**
     * 0 = > prod
     * 1 = > dev
     * @var $_environment
     */
    protected $_environment;

    /**
     * @var $bundleId
     */
    protected $bundleId;

    /**
     * Sender constructor.
     * @param $environment
     * @param $authToken
     * @param $bundleId
     * @throws \Exception
     */
    public function __construct($environment, $authToken, $bundleId)
    {
        if (!array_key_exists($environment, self::ENVIRONMENTS)) {
            throw new \Exception('Invalid environment ' . $environment);
        }
        $this->_environment = $environment;

        if (empty($authToken)) {
            throw new \Exception('Empty auth token');
        }
           $this->authToken = $authToken;
           $this->bundleId = $bundleId;
    }

    /**
     * adds receiver
     * @param $token
     * @throws \Exception
     */
    public function addReceiver($token)
    {
        if (!preg_match('~[a-f0-9]{64}~', $token)) {
            throw new \Exception("Invalid token " . $token);
        }
        $this->receiversTokens[] = $token;
    }

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
     * @param $curl
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
     * @param $payload
     */
    public function sendPush($payload)
    {

        foreach ($this->receiversTokens as $receiversToken) {

            $tokenPartUrl = '/3/device/' . $receiversToken;

            $baseUrl = self::ENVIRONMENTS[$this->_environment];

            $urlToSend = $baseUrl . $tokenPartUrl;

            $this->createHttp2Connection($urlToSend, $payload);
        }
    }

    /**
     * @param $url
     * @param $payload
     * @throws \Exception
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

            var_dump($response);

             if ($response !== false && preg_match('~HTTP/2.0~', $response)) {

            } elseif ($response !== false) {
                 throw new \Exception("No HTTP/2 support on server");
            } else {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);
        } else {
            throw new \Exception("No HTTP/2 support on client");
        }
    }
}