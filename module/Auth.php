<?php

namespace Module;

use Jose\Factory\JWKFactory;
use Jose\Factory\JWSFactory;

class Auth
{
    /**
     * required encoding algorithm
     */
    const ALGORITHM = 'ES256';

    /**
     * @var $authKey
     */
    protected $authKey;

    /**
     * Auth constructor.
     * @param $authKey
     * @throws \Exception
     */
    public function __construct($authKey)
    {
        if (!is_readable($authKey)) {
            throw new \Exception('Can not read auth key');
        }
        $this->authKey = $authKey;
    }

    /**
     * Generating auth token from certificate
     *
     * @param $apnsKeyId
     * @param $secret
     * @param $teamId
     * @return string
     */
    public function setAuthToken($apnsKeyId, $secret, $teamId)
    {
        $key = JWKFactory::createFromKeyFile(
            $this->authKey,
            $secret,
            [
                'kid' => $apnsKeyId,
                'alg' => self::ALGORITHM
            ]
        );

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
            $key,
            $headers
        );
    }

}