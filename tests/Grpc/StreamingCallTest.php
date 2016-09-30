<?php

namespace Grpc;


class StreamingCallTest extends \PHPUnit_Framework_TestCase
{

    public function test_start()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])
            ->getMock();

        $call->expects(static::once())->method('startBatch')
            ->willReturnCallback(function ($input) {
                static::assertArrayHasKey(OP_SEND_INITIAL_METADATA, $input);
                static::assertEquals([], $input[OP_SEND_INITIAL_METADATA]);
            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start();

        static::assertEquals(StreamingCall::STARTED_STATE, $streamingCall->getState());
    }

    public function test_start_with_meta_data()
    {
        $metaData = $this->getMockBuilder(MetaData::class)->disableOriginalConstructor()
            ->setMethods(['toArray'])
            ->getMock();

        $metaData->expects(static::once())
            ->method('toArray')
            ->willReturn(['key' => 'value']);


        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $call->expects(static::once())->method('startBatch')
            ->willReturnCallback(function ($input) {
                static::assertArrayHasKey(OP_SEND_INITIAL_METADATA, $input);
                static::assertEquals(['key' => 'value'], $input[OP_SEND_INITIAL_METADATA]);
            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start($metaData);

        static::assertEquals(StreamingCall::STARTED_STATE, $streamingCall->getState());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_double_start_call()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])
            ->getMock();

        $streamingCall = new StreamingCall($call);

        $streamingCall->start();
        $streamingCall->start();
    }

    public function test_cancel()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['cancel'])
            ->getMock();

        $call->expects(static::once())->method('cancel');

        $streamingCall = new StreamingCall($call);
        $streamingCall->cancel();

        static::assertEquals(StreamingCall::CANCELED_STATE, $streamingCall->getState());
    }

    public function test_setCallCredentials()
    {

        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCredentials'])
            ->getMock();

        $call->expects(static::once())->method('setCredentials');

        $streamingCall = new StreamingCall($call);
        $streamingCall->setCallCredentials(new CallCredentials());
    }

    public function test_write()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $called = false;

        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) use (&$called) {
                if (isset($input[OP_SEND_MESSAGE])) {
                    static::assertEquals(['message' => 'message payload'], $input[OP_SEND_MESSAGE]);
                    $called = true;
                }

            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->write('message payload');

        static::assertTrue($called, 'Start batch was not called with OP_SEND_MESSAGE key');
    }

    public function test_write_with_flags()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();


        $called = false;

        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) use (&$called) {
                if (isset($input[OP_SEND_MESSAGE])) {
                    static::assertEquals(['message' => 'message payload', 'flags' => 12], $input[OP_SEND_MESSAGE]);
                    $called = true;
                }

            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->write('message payload', 12);

        static::assertTrue($called, 'Start batch was not called with OP_SEND_MESSAGE key');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_write_before_start()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()->getMock();

        $streamingCall = new StreamingCall($call);
        $streamingCall->write('message payload', 12);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_read_before_start()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()->getMock();

        $streamingCall = new StreamingCall($call);
        $streamingCall->read();
    }

    /**
     * @expectedException \DomainException
     */
    public function test_read_where_invalid_structure_is_returned_form_call()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->read();
    }

    /**
     * @expectedException \DomainException
     */
    public function test_read_where_no_meta_data_is_returned_from_call()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) {
                if (isset($input[OP_RECV_MESSAGE])) {
                    return (object)['message' => 'return  message'];
                }
                return null;
        });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->read();
    }

    public function test_read()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();


        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) {
                if (isset($input[OP_RECV_MESSAGE])) {
                    return (object)['message' => 'return  message', 'metadata' => ['key' => 'value']];
                }
                return null;
            });

        $streamingCall = new StreamingCall($call);
        $message = $streamingCall->start()->read();

        static::assertEquals('return  message', $message);
        static::assertEquals(['key' => 'value'], $streamingCall->getMetadata());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_readAndClose_before_start()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()->getMock();

        $streamingCall = new StreamingCall($call);
        $streamingCall->readAndClose();
    }

    /**
     * @expectedException \DomainException
     */
    public function test_readAndClose_where_invalid_structure_is_returned_form_call()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->readAndClose();
    }

    /**
     * @expectedException \DomainException
     */
    public function test_readAndClose_where_no_meta_data_is_returned_from_call()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) {
                if (isset($input[OP_RECV_MESSAGE])) {
                    return (object)['message' => 'return  message'];
                }
                return null;
            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->readAndClose();
    }

    public function test_readAndClose()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();


        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) {
                if (isset($input[OP_SEND_CLOSE_FROM_CLIENT])
                    && isset($input[OP_RECV_MESSAGE])
                    && isset($input[OP_RECV_STATUS_ON_CLIENT])
                ) {
                    return (object)[
                        'message' => 'return  message',
                        'status' => (object)[
                            'code' => 0,
                            'details' => ''
                        ],
                        'metadata' => ['key' => 'value']
                    ];
                }
                return null;
            });

        $streamingCall = new StreamingCall($call);
        $message = $streamingCall->start()->readAndClose();

        static::assertEquals(StreamingCall::CLOSED_STATE, $streamingCall->getState());
        static::assertEquals('return  message', $message);
        static::assertEquals(0, $streamingCall->getStatus()->code);
        static::assertEquals('', $streamingCall->getStatus()->details);
        static::assertEquals(['key' => 'value'], $streamingCall->getMetadata());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_close_before_start()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()->getMock();

        $streamingCall = new StreamingCall($call);
        $streamingCall->close();
    }

    public function test_close()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();
        $called = false;
        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) use (&$called) {
                if (isset($input[OP_SEND_CLOSE_FROM_CLIENT])) {
                    $called = true;
                }
            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->close();

        static::assertTrue($called, 'Start batch was not called with OP_SEND_CLOSE_FROM_CLIENT key');
        static::assertEquals(StreamingCall::CLOSED_STATE, $streamingCall->getState());
    }

    public function test_getStatus()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $streamingCall = new StreamingCall($call);
        $status = $streamingCall->getStatus();

        self::assertEquals(-1, $status->code);
        self::assertEquals('No status available', $status->details);
    }

    public function test_getStatus_from_server_response()
    {
        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) {
                if (isset($input[OP_RECV_STATUS_ON_CLIENT])) {
                    return (object)[
                        'status' => (object)['code' => 1, 'details' => 'OK']
                    ];
                }

                return null;
            });

        $streamingCall = new StreamingCall($call);
        $status = $streamingCall->start()->getStatus();

        self::assertEquals(1, $status->code);
        self::assertEquals('OK', $status->details);
    }

    /**
     * @expectedException \DomainException
     */
    public function test_getStatus_invalid_server_response()
    {

        $call = $this->getMockBuilder(Call::class)
            ->disableOriginalConstructor()
            ->setMethods(['startBatch'])->getMock();

        $call->expects(static::exactly(2))->method('startBatch')
            ->willReturnCallback(function ($input) {
                if (isset($input[OP_RECV_STATUS_ON_CLIENT])) {
                    return null;
                }
            });

        $streamingCall = new StreamingCall($call);
        $streamingCall->start()->getStatus();

    }

}