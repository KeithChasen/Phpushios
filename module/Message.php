<?php
namespace Phpushios;

use PhpushiousException;

class Message
{
    /**
     * apple aps namespace
     */
    const APS_NAMESPACE = 'aps';
    /**
     * @var string
     */
    protected $payload_data;

    /**
     * @var integer
     */
    protected $badgeNum;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $sound;

    /**
     * @var integer
     */
    protected $contentAvailable;

    /**
     * @var string
     */
    protected $category;

    /**
     * @var integer
     */
    protected $mutableContent;

    /**
     * @var array
     */
    protected $customProperties;

    /**
     * Sets and returns payload
     *
     * @return string
     */
    public function setPayload()
    {
        $this->payload_data = [self::APS_NAMESPACE => []];
        if (isset($this->text)) {
            $this->payload_data[self::APS_NAMESPACE ]['alert'] = $this->text;
        }
        if (isset($this->sound)) {
            $this->payload_data[self::APS_NAMESPACE ]['sound'] = $this->sound;
        }
        if (isset($this->badgeNum)) {
            $this->payload_data[self::APS_NAMESPACE ]['badge'] = $this->badgeNum;
        }
        if (isset($this->contentAvailable)) {
            $this->payload_data[self::APS_NAMESPACE ]['content-available'] = $this->contentAvailable;
        }
        if (isset($this->category)) {
            $this->payload_data[self::APS_NAMESPACE ]['category'] = $this->category;
        }
        if (isset($this->mutableContent)) {
            $this->payload_data[self::APS_NAMESPACE ]['mutable-content'] = $this->mutableContent;
        }

        if (!empty($this->customProperties)) {
            foreach ($this->customProperties as $key => $value) {
                $this->payload_data[self::APS_NAMESPACE][$key] = $value;
            }
        }

        $this->payload_data = json_encode($this->payload_data);

        return $this->payload_data;
    }

    /**
     * Sets the value of badge
     *
     * @param integer $number
     * @throws PhpushiousException
     */
    public function setBadgeNumber($number)
    {
        if (!is_int($number) && $number >= 0) {
            throw new PhpushiousException(
                "Invalid badge number " . $number
            );
        }
        $this->badgeNum = $number;
    }

    /**
     * Sets content-available parameter to configure silent push
     *
     * @param bool $contentAvailable
     * @throws PhpushiousException
     */
    public function setContentAvailable($contentAvailable = false)
    {
        if (!is_bool($contentAvailable)) {
            throw new PhpushiousException(
                "Invalid content-available value " . $contentAvailable
            );
        }
        $this->contentAvailable = $contentAvailable ? (int)$contentAvailable : null;
    }

    /**
     * Sets category
     *
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = !empty($category) ? $category : null;
    }

    /**
     * Sets mutable-content key for extension on iOS10
     *
     * @param bool $mutableContent
     * @throws PhpushiousException
     */
    public function setMutableContent($mutableContent = false)
    {
        if (!is_bool($mutableContent)) {
            throw new PhpushiousException(
                "Invalid mutable-content value " . $mutableContent
            );
        }
        $this->mutableContent = $mutableContent ? (int)$mutableContent : null;
    }

    /**
     * Sets alert message
     *
     * @param string $message
     */
    public function setAlert($message)
    {
        $this->text = $message;
    }

    /**
     * Sets sound
     *
     * @param string $sound
     */
    public function setSound($sound)
    {
        $this->sound = $sound;
    }

    /**
     * Sets custom property
     *
     * @param string $name
     * @param string $value
     *
     * @throws PhpushiousException
     */
    public function setCustomProperty($name, $value)
    {
        if (trim($name) == self::APS_NAMESPACE) {
            throw new PhpushiousException(
                'Property ' . $name . ' can not be used'
            );
        }
        $this->customProperties[$name] = $value;
    }
}
