<?php
/**
 * Created by PhpStorm.
 * User: keithchasen
 * Date: 25.11.16
 * Time: 16:52
 */

namespace Module;


class Message
{
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
     * setsPayload
     */
    public function setPayload()
    {
        $this->payload_data = ["aps" => []];
        if (isset($this->text)) {
            $this->payload_data['aps']['alert'] = $this->text;
        }
        if (isset($this->sound)) {
            $this->payload_data['aps']['sound'] = $this->sound;
        }
        if (isset($this->badgeNum)) {
            $this->payload_data['aps']['badge'] = $this->badgeNum;
        }
        $this->payload_data = json_encode($this->payload_data);
        return $this->payload_data;
    }

    /**
     * @param $number
     * @throws \Exception
     */
    public function setBadgeNumber($number)
    {
        if (!is_int($number) && $number >= 0) {
            throw new \Exception("Invalid badge number " . $number);
        }
        $this->badgeNum = $number;
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
}