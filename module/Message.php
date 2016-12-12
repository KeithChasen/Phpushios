<?php declare(strict_types=1);

/**
 * Library for sending iOS push notifications using p8 certificate
 *
 * PHP version 7
 *
 * @category Message
 * @package  Phpushios
 * @author   Keith Chasen <keithchasen89@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  GIT: $Id$
 * @link     https://github.com/KeithChasen/Phpushios
 */

namespace Phpushios;

use PhpushiosException;

/**
 * Sets payload of the push
 *
 * @category Message
 * @package  Phpushios
 * @author   Keith Chasen <keithchasen89@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  Release: 1.0.0
 * @link     https://github.com/KeithChasen/Phpushios
 */
class Message
{
    /**
     * Apple aps namespace
     */
    const APS_NAMESPACE = 'aps';

    /**
     * Payload to be sent with push notification
     *
     * @var string
     */
    protected $payloadData;

    /**
     * Badge value to be sent
     *
     * @var integer
     */
    protected $badgeNum;

    /**
     * Alert text to be sent
     *
     * @var string
     */
    protected $text;

    /**
     * Sound value to be sent
     *
     * @var string
     */
    protected $sound;

    /**
     * Value of content-available parameter
     *
     * @var integer
     */
    protected $contentAvailable;

    /**
     * Category to be sent
     *
     * @var string
     */
    protected $category;

    /**
     * Value of mutable-content parameter
     *
     * @var integer
     */
    protected $mutableContent;

    /**
     * Array of custom properties to be sent
     *
     * @var array
     */
    protected $customProperties;

    /**
     * Sets and returns payload
     *
     * @return string
     */
    public function setPayload() : string
    {
        $this->payloadData = [self::APS_NAMESPACE => []];
        if (isset($this->text)) {
            $this->payloadData[self::APS_NAMESPACE ]['alert']
                = $this->text;
        }
        if (isset($this->sound)) {
            $this->payloadData[self::APS_NAMESPACE ]['sound']
                = $this->sound;
        }
        if (isset($this->badgeNum)) {
            $this->payloadData[self::APS_NAMESPACE ]['badge']
                = $this->badgeNum;
        }
        if (isset($this->contentAvailable)) {
            $this->payloadData[self::APS_NAMESPACE ]['content-available']
                = $this->contentAvailable;
        }
        if (isset($this->category)) {
            $this->payloadData[self::APS_NAMESPACE ]['category']
                = $this->category;
        }
        if (isset($this->mutableContent)) {
            $this->payloadData[self::APS_NAMESPACE ]['mutable-content']
                = $this->mutableContent;
        }

        if (!empty($this->customProperties)) {
            foreach ($this->customProperties as $key => $value) {
                $this->payloadData[self::APS_NAMESPACE][$key] = $value;
            }
        }

        $this->payloadData = json_encode($this->payloadData);

        return $this->payloadData;
    }

    /**
     * Sets the value of badge
     *
     * @param int $badgeNumber Badge value to be sent
     *
     * @throws PhpushiosException  Invalid badge number was used
     *
     * @return void
     */
    public function setBadgeNumber(int $badgeNumber)
    {
        if (!is_int($badgeNumber) || $badgeNumber < 0) {
            throw new PhpushiosException(
                "Invalid badge number " . $badgeNumber
            );
        }
        $this->badgeNum = $badgeNumber;
    }

    /**
     * Sets content-available parameter to configure silent push
     *
     * @param bool $contentAvailable Value to set content-available parameter
     *                               Use true to set content-available = 1
     *                               to enable silent push
     *
     * @throws PhpushiosException    Invalid content-available value was used
     *
     * @return void
     */
    public function setContentAvailable(bool $contentAvailable = false)
    {
        if (!is_bool($contentAvailable)) {
            throw new PhpushiosException(
                "Invalid content-available value " . $contentAvailable
            );
        }
        $this->contentAvailable = $contentAvailable ? (int)$contentAvailable : null;
    }

    /**
     * Sets category
     *
     * @param string $category Category to be sent
     *
     * @return void
     */
    public function setCategory(string $category)
    {
        $this->category = !empty($category) ? $category : null;
    }

    /**
     * Sets mutable-content key for extension on iOS10
     *
     * @param bool $mutableContent Value to set mutable-content parameter
     *                              Use true to set mutable-content = 1
     *
     * @throws PhpushiosException  Invalid mutable-content value was used
     *
     * @return void
     */
    public function setMutableContent(bool $mutableContent = false)
    {
        if (!is_bool($mutableContent)) {
            throw new PhpushiosException(
                "Invalid mutable-content value " . $mutableContent
            );
        }
        $this->mutableContent = $mutableContent ? (int)$mutableContent : null;
    }

    /**
     * Sets alert message
     *
     * @param string $message Alert message to be sent
     *
     * @return void
     */
    public function setAlert(string $message)
    {
        $this->text = $message;
    }

    /**
     * Sets sound
     *
     * @param string $sound Sound to be sent
     *
     * @return void
     */
    public function setSound(string $sound)
    {
        $this->sound = $sound;
    }

    /**
     * Sets custom property
     *
     * @param string $name  Name of custom property
     * @param string $value Value of custom property
     *
     * @throws PhpushiosException "aps" reserved namespace
     *                             was used for custom property name
     *
     * @return void
     */
    public function setCustomProperty(string $name, string $value)
    {
        if (trim($name) == self::APS_NAMESPACE) {
            throw new PhpushiosException(
                'Property ' . $name . ' can not be used'
            );
        }
        $this->customProperties[$name] = $value;
    }
}
