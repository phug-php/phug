<?php

class Date extends DateTime
{
    public function __construct($time = 'now')
    {
        $timezone = new DateTimeZone('UTC');

        try {
            parent::__construct($time, $timezone);
        } catch (Exception $exception) {
            parent::__construct('now', $timezone);
            $this->setTimestamp($time);
        }
    }

    public function __toString()
    {
        return $this->format('Y-m-d\TH:i:s').'.000Z';
    }
}
