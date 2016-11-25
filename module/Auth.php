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
     * @param $apnsAuthKey
     * @throws \Exception
     */
    public function __construct(
        $apnsAuthKey
    ) {
        if (!is_readable($apnsAuthKey)) {
            throw new \Exception('Can not read auth key');
        }
        $this->authKey = $apnsAuthKey;
    }

    /**
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