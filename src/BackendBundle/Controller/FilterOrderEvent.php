<?php


namespace BackendBundle\Controller;

use Symfony\Component\EventDispatcher\Event;

class FilterOrderEvent extends Event
{
    protected $data;

    /**
     * FilterOrderEvent constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


}