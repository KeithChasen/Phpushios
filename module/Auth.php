<?php declare(strict_types=1);

/**
 * Library for sending iOS push notifications using p8 certificate
 *
 * PHP version 7
 *
 * @category Authentication
 * @package  Phpushios
 * @author   Keith Chasen <keithchasen89@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  GIT: $Id$
 * @link     https://github.com/KeithChasen/Phpushios
 */

namespace Phpushios;

use Jose\Factory\JWKFactory;
use Jose\Factory\JWSFactory;
use PhpushiosException;

/**
 * Creates authorization token
 *
 * @category Authentication
 * @package  Phpushios
 * @author   Keith Chasen <keithchasen89@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  Release: 1.0.0
 * @link     https://github.com/KeithChasen/Phpushios
 */
class Auth
{
    /**
     * Required encoding algorithm
     */
    const ALGORITHM = 'ES256';

    /**
     * Path to the p8 authorization key
     *
     * @var string
     */
    protected $authKey;

    /**
     * Auth constructor.
     *
     * @param string $authKey Path to the p8 authorization key
     *
     * @throws PhpushiosException Auth key is not readable
     */
    public function __construct(string $authKey)
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
     * @param string $apnsKeyId     Authorization key id
     * @param string $authKeySecret Secret phrase for authorization key
     * @param string $teamId        Team Id
     *
     * @return string
     */
    public function setAuthToken(
        string $apnsKeyId,
        string $authKeySecret,
        string $teamId
    ) : string {
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
