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
     * @var $payload_data
     */
    protected $payload_data;

    /**
     * @var $badgeNum
     */
    protected $badgeNum;

    /**
     * @var $text
     */
    protected $text;

    /**
     * @var $sound
     */
    protected $sound;

    /**
     * @var $contentAvailable
     */
    protected $contentAvailable;

    /**
     * @var $category
     */
    protected $category;

    /**
     * @var $mutableContent
     */
    protected $mutableContent;

    /**
     * @var $customProperties
     */
    protected $customProperties = [];

    /**
     * sets and returns payload
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
     * @param $number
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
     * @param $category
     */
    public function setCategory($category)
    {
        $this->category = !empty($category) ? $category : null;
    }

    /**
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
     * @param $message
     */
    public function setAlert($message)
    {
        $this->text = $message;
    }

    /**
     * @param $sound
     */
    public function setSound($sound)
    {
        $this->sound = $sound;
    }

    /**
     * @param $name
     * @param $value
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