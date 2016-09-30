<?php

namespace Grpc;


class MetaData
{
    protected $data = [];

    /**
     * MetaData constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->addData($key, $value);
        }
    }


    /**
     * @param string $key
     * @param string $value
     */
    public function addData($key, $value)
    {
        if (!preg_match('/^[A-Za-z\d_-]+$/', $key)) {
            throw new \InvalidArgumentException(
                'Metadata keys must be nonempty strings containing only ' .
                'alphanumeric characters, hyphens and underscores');
        }

        $this->data[strtolower($key)] = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}