<?php

namespace Module;

use Firebase\JWT\JWT;

class Sender
{
    /**
     * required encoding algorithm
     */
    const ALGORITHM = 'ES256';

//'tls://gateway.push.apple.com:2195', // Production environment
//'tls://gateway.sandbox.push.apple.com:2195' // Sandbox environment

//'api.push.apple.com:443',
//'api.development.push.apple.com:443'
    /**
     * 0 = > prod
     * 1 = > dev
     */
    const ENVIRONMENTS = [
        'api.push.apple.com',
        'api.development.push.apple.com'
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
     * @var $urlToSend
     * dev or prod url with users token
     */
    protected $urlToSend;

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
     * @throws \Exception
     */
    public function __construct(
        $environment,
        $apnsAuthKey,
        $apnsKeyId,
        $teamId,
        $bundleId
    ) {
        if (!array_key_exists($environment, self::ENVIRONMENTS)) {
            throw new \Exception('Invalid evironment ' . $environment);
        }
        $this->_environment = $environment;

        if (!is_readable($apnsAuthKey)) {
            throw new \Exception('Can not read auth key');
        }
        $this->bundleId = $bundleId;

        $secret = file_get_contents($apnsAuthKey);

        $this->setToken($apnsKeyId, $teamId, time(), $secret);
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
     * ID of the generated key
     * @param $apns_key_id
     *
     * developer team id
     * @param $teamId
     *
     * time()
     * @param $issueAt
     *
     * $secret = file_get_contents($apns_auth_key);
     * @param $secret
     */
    public function setToken($apnsKeyId, $teamId, $issueAt, $secret)
    {
        $payload = [
          'iss' => $teamId,
            'iat' => $issueAt
        ];

        $headers = [
            'alg' => self::ALGORITHM,
            'kid' => $apnsKeyId
        ];

        $this->token = JWT::encode($payload, $secret, self::ALGORITHM, null, $headers);
    }

    /**
     * sets request headers
     */
    public function setRequestHeaders()
    {
        $this->requestHeaders = [
            'apns-expiration' => '0',
            'apns-priority' => '10',
            'apns-topic' => $this->bundleId,
            'authorization' => 'bearer ' . $this->token
        ];
    }

    /**
     * sets payload
     *
     * @param $message
     */
    public function setPayload($message)
    {
        $this->payload_data = [
            'aps' => [
                'alert' => $message
            ]
        ];
    }

    /**
     * @param $message
     * @param $userToken
     */
    public function sendPush($message, $userToken)
    {
        $this->setPayload($message);
        $this->setRequestHeaders();

        $this->urlToSend = self::ENVIRONMENTS[$this->_environment] . '/3/device/' . $userToken;

        $this->createHttp2Connection($this->urlToSend);
    }

    public function createHttp2Connection($url)
    {
        if (curl_version()['features'] & CURL_VERSION_HTTP2 !== 0) {
          $ch = curl_init();
            curl_setopt_array($ch,
                [
                    CURLOPT_URL => $url,
                    CURLOPT_PORT => 443,
                    CURLOPT_HTTPHEADER => $this->requestHeaders,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $this->payload_data,
                    CURLOPT_HEADER => true, //1
                    CURLOPT_NOBODY => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0
                ]
            );
            $response = curl_exec($ch);

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