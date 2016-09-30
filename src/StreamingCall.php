<?php

namespace Grpc;


class StreamingCall
{
    const INITIALIZED_STATE = 'initialized';
    const STARTED_STATE = 'started';
    const CANCELED_STATE = 'canceled';
    const CLOSED_STATE = 'closed';

    /**
     * @var Call
     */
    protected $call;

    /**
     * @var array
     */
    protected $metaData = null;

    /**
     * @var string
     */
    protected $state = self::INITIALIZED_STATE;
    /**
     * @var Status
     */
    protected $status;

    /**
     * UnaryCall constructor.
     * @param Call $call
     */
    public function __construct(Call $call)
    {
        $this->call = $call;
    }

    /**
     * @return array The metadata sent by the server.
     */
    public function getMetadata()
    {
        return $this->metaData === null ? [] : $this->metaData;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * Cancels the call.
     */
    public function cancel()
    {
        $this->call->cancel();
        $this->state = self::CANCELED_STATE;
    }

    /**
     * Set the CallCredentials for the underlying Call
     *
     * @param CallCredentials $callCredentials The CallCredentials
     */
    public function setCallCredentials($callCredentials)
    {
        $this->call->setCredentials($callCredentials);
    }


    /**
     * @param MetaData $metadata
     * @return $this
     */
    public function start($metadata = null)
    {
        if ($this->state === self::STARTED_STATE) {
            throw new \RuntimeException('Connection already started');
        }

        $this->call->startBatch([
            OP_SEND_INITIAL_METADATA => null === $metadata ? [] : $metadata->toArray(),
        ]);

        $this->state = self::STARTED_STATE;

        return $this;
    }


    /**
     * @param string $message
     * @param null $flags
     * @return $this
     * @throws \RuntimeException
     */
    public function write($message, $flags = null)
    {
        if ($this->state !== self::STARTED_STATE) {
            throw new \RuntimeException('Connection needs to be started before we can write to it');
        }

        $messageArray = [
            'message' => $message
        ];

        $flags === null ?: $messageArray['flags'] = $flags;

        $this->call->startBatch([
            OP_SEND_MESSAGE => $messageArray
        ]);

        return $this;
    }

    /**
     * Reads the next value from the server.
     *
     * @return string
     * @throws \RuntimeException
     */
    public function read()
    {
        if ($this->state !== self::STARTED_STATE) {
            throw new \RuntimeException('Connection needs to be started before we can read from it');
        }

        $batch = [
            OP_RECV_MESSAGE => true
        ];

        if ($this->metaData === null) {
            $batch[OP_RECV_INITIAL_METADATA] = true;
        }

        $event = $this->call->startBatch($batch);

        if (!is_object($event) || !property_exists($event, 'message')) {
            throw new \DomainException('Invalid call response structure');
        }

        if ($this->metaData === null) {

            if (!property_exists($event, 'metadata')) {
                throw new \DomainException('Invalid call response structure');
            }

            $this->metaData = $event->metadata;
        }

        return $event->message;
    }

    /**
     * @return string
     * @throws \RuntimeException
     * @throws \DomainException
     */
    public function readAndClose()
    {
        if ($this->state !== self::STARTED_STATE) {
            throw new \RuntimeException('Connection needs to be started before we can read from it');
        }

        $batch = [
            OP_SEND_CLOSE_FROM_CLIENT => true,
            OP_RECV_MESSAGE => true,
            OP_RECV_STATUS_ON_CLIENT => true,
        ];

        if ($this->metaData === null) {
            $batch[OP_RECV_INITIAL_METADATA] = true;
        }

        $event = $this->call->startBatch($batch);

        if (!is_object($event) || !property_exists($event, 'message')) {
            throw new \DomainException('Missing message data in call response structure');
        }

        // validate response
        if ($this->metaData === null) {

            if (!property_exists($event, 'metadata')) {
                throw new \DomainException('Missing meta data in response structure');
            }

            $this->metaData = $event->metadata;
        }

        $this->setStatusFromCallResponse($event);

        $this->state = self::CLOSED_STATE;

        return $event->message;
    }

    /**
     * Closes te connection
     * @throws \RuntimeException
     */
    public function close()
    {
        if ($this->state !== self::STARTED_STATE) {
            throw new \RuntimeException('Connection needs to be started before we can close it');
        }

        $this->call->startBatch([OP_SEND_CLOSE_FROM_CLIENT => true]);

        $this->state = self::CLOSED_STATE;

        return $this;
    }

    /**
     * @param object $event
     */
    protected function setStatusFromCallResponse($event)
    {
        if (!is_object($event)
            || !property_exists($event, 'status')
            || !property_exists($event->status, 'code')
            || !property_exists($event->status, 'details')
        ) {
            throw new \DomainException('Call response must contain status data');
        }

        $this->status = new Status($event->status->code, $event->status->details);
    }

    /**
     * Wait for the server to send the status, and return it.
     *
     * @return Status
     * @throws \RuntimeException
     */
    public function getStatus()
    {

        if (null === $this->status) {

            if ($this->state === self::STARTED_STATE) {
                $event = $this->call->startBatch([
                    OP_RECV_STATUS_ON_CLIENT => true,
                ]);

                $this->setStatusFromCallResponse($event);
            } else {
                $this->status = new Status(-1, 'No status available');
            }
        }

        return $this->status;
    }
}