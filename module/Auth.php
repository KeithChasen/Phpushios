<?php

namespace Phpushios;

use Jose\Factory\JWKFactory;
use Jose\Factory\JWSFactory;
use PhpushiousException;

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
     * @throws PhpushiousException
     */
    public function __construct($authKey)
    {
        if (!is_readable($authKey)) {
            throw new PhpushiousException(
                'Can not read auth key'
            );
        }
        $this->authKey = $authKey;
    }

    /**
     * Generating auth token from certificate
     *
     * @param $apnsKeyId
     * @param $authKeySecret
     * @param $teamId
     * @return string
     */
    public function setAuthToken($apnsKeyId, $authKeySecret, $teamId)
    {
        $secret = JWKFactory::createFromKeyFile(
            $this->authKey,
            $authKeySecret,
            [
                'alg' => self::ALGORITHM,
                'kid' => $apnsKeyId
            ]
        );

        $claim = [
            'iss' => $teamId,
            'iat' => time()
        ];

        $header = [
            'alg' => self::ALGORITHM,
            'kid' => $apnsKeyId
        ];

        return JWSFactory::createJWSToCompactJSON(
            $claim,
            $secret,
            $header
        );
    }

}