<?php

namespace Module;

use Jose\Factory\JWKFactory;
use Jose\Factory\JWSFactory;


class Sender
{
    /**
     * required encoding algorithm
     */
    const ALGORITHM = 'ES256';

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
    private $token;

    /**
     * @var array
     * array of user tokens
     */
    protected $receiversTokens = [];

    /**
     * @var $request_headers
     */
    protected $requestHeaders;

    /**
     * @var $payload_data
     */
    protected $payload_data;

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
     * @param $apnsAuthKey
     * @param $apnsKeyId
     * @param $teamId
     * @param $bundleId
     * @param $secret
     * @throws \Exception
     */
    public function __construct(
        $environment,
        $apnsAuthKey,
        $apnsKeyId,
        $teamId,
        $bundleId,
        $secret
    ) {
        if (!array_key_exists($environment, self::ENVIRONMENTS)) {
            throw new \Exception('Invalid environment ' . $environment);
        }
        $this->_environment = $environment;

        if (!is_readable($apnsAuthKey)) {
            throw new \Exception('Can not read auth key');
        }
        $this->bundleId = $bundleId;
        $this->token = $this->setToken($apnsAuthKey, $apnsKeyId, $secret, $teamId);
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

    /**
     * @param $apnsAuthKey
     * @param $apnsKeyId
     * @param $secret
     * @param $teamId
     * @return string
     */
    public function setToken($apnsAuthKey, $apnsKeyId, $secret, $teamId)
    {
        $privateECKey = JWKFactory::createFromKeyFile($apnsAuthKey, $secret, [
            'kid' => $apnsKeyId,
            'alg' => self::ALGORITHM,
            'use' => 'sig'
        ]);

        $claims = [
            'iss' => $teamId,
            'iat' => time()
        ];

        $headers = [
            'alg' => self::ALGORITHM,
            'kid' => $apnsKeyId
        ];

        return JWSFactory::createJWSToCompactJSON(
            $claims,
            $privateECKey,
            $headers
        );
    }

    /**
     * @param $curl
     */
    public function auth($curl)
    {
        $this->requestHeaders = [
            'apns-topic: ' . $this->bundleId,
            'Authorization: bearer ' . $this->token
        ];

        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->requestHeaders);
    }

    /**
     * sets payload
     *
     * @param $message
     */
    public function setPayload($message)
    {
        $this->payload_data = '{"aps":{"alert":"' . $message . '","sound":"default"}}';
    }

    /**
     * @param $message
     */
    public function sendPush($message)
    {
        foreach ($this->receiversTokens as $receiversToken) {
            $this->setPayload($message);

            $path = '/3/device/' . $receiversToken;

            $urlToSend = self::ENVIRONMENTS[$this->_environment] . $path;

            $this->createHttp2Connection($urlToSend);
        }
    }

    public function createHttp2Connection($url)
    {
        if (curl_version()['features'] & CURL_VERSION_HTTP2 !== 0) {

          $ch = curl_init();

            $this->auth($ch);

            curl_setopt_array($ch,
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                    CURLOPT_HTTPHEADER, $this->requestHeaders,
                    CURLOPT_URL => $url,
                    CURLOPT_PORT => 443,
                    CURLOPT_HEADER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $this->payload_data,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30
                ]
            );

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            var_dump($response);
            var_dump($httpcode);

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