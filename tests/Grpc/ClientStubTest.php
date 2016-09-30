<?php

namespace Grpc;


class ClientStubTest extends \PHPUnit_Framework_TestCase
{

    public function test_close()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['close'])->getMock();

        $channel->expects(static::once())
            ->method('close');

        $client = new ClientStub($channel);
        $client->close();
    }

    public function test_getTarget()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTarget'])->getMock();

        $channel->expects(static::once())
            ->method('getTarget');

        $client = new ClientStub($channel);
        $client->getTarget();
    }

    public function test_waitForReady_channel_is_ready()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnectivityState', 'watchConnectivityState'])->getMock();

        $channel->expects(static::once())
            ->method('getConnectivityState')
            ->willReturn(CHANNEL_READY);

        $client = new ClientStub($channel);
        $client->waitForReady(100);
    }

    public function test_waitForReady_waiting_for_channel_to_be_ready()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnectivityState', 'watchConnectivityState'])->getMock();

        $channel->expects(static::atLeastOnce())
            ->method('getConnectivityState')
            ->willReturnCallback(function ($connect) {
                if ($connect) {
                    return 0;
                }

                return CHANNEL_READY;
            });
        $channel->expects(static::atLeastOnce())
            ->method('watchConnectivityState')
            ->willReturn(true);

        $client = new ClientStub($channel);
        $connected = $client->waitForReady(0);

        static::assertTrue($connected);
    }

    public function test_waitForReady_timeout()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnectivityState', 'watchConnectivityState'])->getMock();

        $channel->expects(static::exactly(2))
            ->method('getConnectivityState')
            ->willReturn(0);

        $channel->expects(static::once())
            ->method('watchConnectivityState')
            ->willReturn(false);

        $client = new ClientStub($channel);
        $connected = $client->waitForReady(0);

        static::assertFalse($connected);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test__waitForReady_failed_connection()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnectivityState', 'watchConnectivityState'])->getMock();

        $channel->expects(static::once())
            ->method('getConnectivityState')
            ->willReturn(CHANNEL_FATAL_FAILURE);

        $client = new ClientStub($channel);
        $connected = $client->waitForReady(0);

        static::assertFalse($connected);
    }
}