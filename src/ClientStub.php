<?php

namespace Grpc;


class ClientStub
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * ClientStub constructor.
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Close the communication channel associated with this stub.
     */
    public function close()
    {
        $this->channel->close();
    }


    /**
     * @return string The URI of the endpoint.
     */
    public function getTarget()
    {
        return $this->channel->getTarget();
    }


    /**
     * @param int $timeout in microseconds
     *
     * @return bool true if channel is ready
     * @throw \RuntimeException if channel is in FATAL_ERROR state
     */
    public function waitForReady($timeout = 0)
    {
        $newState = $this->channel->getConnectivityState(true);
        if ($this->checkConnectivityState($newState)) {
            return true;
        }

        $now = Timeval::now();
        $delta = new Timeval($timeout);
        $deadline = $now->add($delta);

        while ($this->channel->watchConnectivityState($newState, $deadline)) {
            // state has changed before deadline
            $newState = $this->channel->getConnectivityState(false);
            if ($this->checkConnectivityState($newState)) {
                return true;
            }
        }
        // deadline has passed
        $newState = $this->channel->getConnectivityState(false);

        return $this->checkConnectivityState($newState);
    }

    /**
     * @param $newState
     * @return bool
     * @throws \RuntimeException
     */
    private function checkConnectivityState($newState)
    {
        if ($newState == CHANNEL_READY) {
            return true;
        }
        if ($newState == CHANNEL_FATAL_FAILURE) {
            throw new \RuntimeException('Failed to connect to server');
        }

        return false;
    }


}