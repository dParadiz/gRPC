<?php

namespace Grpc;


class MetaDataTest extends \PHPUnit_Framework_TestCase
{
    public function test_constructor_data_input()
    {
        $input = ['test_key' => 'test_value'];
        $metaData = new MetaData($input);

        static::assertEquals($input, $metaData->toArray());
    }

    public function test_addData()
    {
        $metaData = new MetaData();

        $metaData->addData('test_key', 'test_value');

        static::assertEquals(['test_key' => 'test_value'], $metaData->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_addData_with_invalid_key_chars()
    {
        $metaData = new MetaData();

        $metaData->addData('test_key**', 'test_value');
    }
}