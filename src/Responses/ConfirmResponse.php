<?php

namespace Ixolit\Moreify\Responses;

/**
 * @package Moreify
 */
class ConfirmResponse
{
    /**
     * @var boolean
     */
    private $success;

    /**
     * @param boolean $success
     */
    public function __construct($success) {
        $this->setSuccess($success);
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param boolean $success
     * @return ConfirmResponse
     */
    public function setSuccess($success)
    {
        $this->success = (boolean) $success;
        return $this;
    }
}