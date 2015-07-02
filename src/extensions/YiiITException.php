<?php

class YiiITException extends Exception
{
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct("YiiImageTransfer Exception: $message", $code, $previous);
    }
}
