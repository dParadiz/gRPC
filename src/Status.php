<?php

namespace Grpc;


class Status
{
    /**
     * @var int
     */
    public $code;
    /**
     * @var string
     */
    public $details;

    /**
     * Status constructor.
     * @param int $code
     * @param string $details
     */
    public function __construct($code, $details)
    {
        $this->code = $code;
        $this->details = $details;
    }


}