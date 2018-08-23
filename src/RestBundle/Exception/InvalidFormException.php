<?php
namespace RestBundle\Exception;

class InvalidFormException extends \RuntimeException
{
    protected $message;

    public function __construct($message = 'Invalid submitted data', $code = 400)
    {
        $this->message = array('message' => $message, 'status' => $code);
    }

    /**
     * @return array|null
     */
    public function getError()
    {
        return $this->message;
    }
}