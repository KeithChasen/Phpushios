<?php
namespace Phpushios;

use Jose\Factory\JWKFactory;
use Jose\Factory\JWSFactory;
use PhpushiosException;

class Auth
{
    /**
     * required encoding algorithm
     */
    const ALGORITHM = 'ES256';

    /**
     * @var string $authKey
     */
    protected $authKey;

    /**
     * Auth constructor.
     *
     * @param string $authKey
     * @throws PhpushiosException
     */
    public function __construct($authKey)
    {
        if (!is_readable($authKey)) {
            throw new PhpushiosException(
                'Can not read auth key'
            );
        }
        $this->authKey = $authKey;
    }

    /**
     * Generating authorization token from certificate
     *
     * @param string $apnsKeyId
     * @param string $authKeySecret
     * @param string $teamId
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
