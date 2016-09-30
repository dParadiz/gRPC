<?php

namespace Grpc {

    const OP_SEND_INITIAL_METADATA = 0;
    const OP_SEND_MESSAGE = 1;
    const OP_SEND_CLOSE_FROM_CLIENT = 2;
    const OP_RECV_INITIAL_METADATA = 4;
    const OP_RECV_MESSAGE = 5;
    const OP_RECV_STATUS_ON_CLIENT = 6;

    const CHANNEL_READY = 2;
    const CHANNEL_FATAL_FAILURE = 4;

    class Call
    {
        /**
         * @param Channel $channel The channel to associate the call with.
         *                             Must not be closed.
         * @param string $method The method to call
         * @param Timeval $deadline The deadline for completing the call
         * @param string $hostOverride The host is set by user (optional)
         *
         */
        public function __construct($channel, $method, $deadline, $hostOverride = NULL)
        {
        }

        /**
         * Start a batch of RPC actions.
         * @param array $array Array of actions to take
         * @return object Object with results of all actions
         */
        public function startBatch($array)
        {
        }

        /**
         * Get the endpoint this call/stream is connected to
         * @return string The URI of the endpoint
         */
        public function getPeer()
        {

        }

        /**
         * Cancel the call. This will cause the call to end with STATUS_CANCELLED
         * if it has not already ended with another status.
         * @return void
         */
        public function cancel()
        {

        }

        /**
         * Set the CallCredentials for this call.
         * @param CallCredentials $creds
         * @return int
         */
        public function setCredentials($creds)
        {

        }
    }

    class CallCredentials
    {
        /**
         * Create composite credentials from two existing credentials.
         * @param CallCredentials $cred1 The first credential
         * @param CallCredentials $cred2 The second credential
         * @return CallCredentials The new composite credentials object
         */
        public static function createComposite($cred1, $cred2)
        {

        }

        /**
         * Create a call credentials object from the plugin API
         * @param callable $fci The callback function
         * @return CallCredentials The new call credentials object
         */
        public static function createFromPlugin($fci)
        {

        }
    }

    class Channel
    {
        /**
         * Channel constructor.
         * @param $host
         * @param array $properties
         */
        public function __construct($host, array $properties = [])
        {
        }

        /**
         * @return String
         */
        public function getTarget()
        {

        }

        /**
         * @param bool $tryToConnect
         * @return int
         */
        public function getConnectivityState($tryToConnect = false)
        {

        }

        /**
         * @param int $lastState
         * @param Timeval $deadline
         * @return bool
         */
        public function watchConnectivityState($lastState, $deadline)
        {

        }

        public function close()
        {

        }
    }

    class ChannelCredentials
    {

        /**
         * Set default roots pem.
         * @param string $pemRoots PEM encoding of the server root certificates
         * @return void
         */

        public static function setDefaultRootsPem($pemRoots)
        {

        }

        /**
         * Create a default channel credentials object.
         * @return ChannelCredentials The new default channel credentials object
         */
        public static function createDefault()
        {

        }

        /**
         * Create SSL credentials.
         * @param string $pemRootCerts PEM encoding of the server root certificates
         * @param string $privateKey PEM encoding of the client's private key (optional)
         * @param string $certsChain PEM encoding of the client's certificate chain (optional)
         * @return ChannelCredentials The new SSL credentials object
         */
        public static function createSsl($pemRootCerts, $privateKey = NULL, $certsChain = NULL)
        {

        }

        /**
         * Create composite credentials from two existing credentials.
         * @param ChannelCredentials $cred1 The first credential
         * @param CallCredentials $cred2 The second credential
         * @return ChannelCredentials The new composite credentials object
         */
        public static function createComposite($cred1, $cred2)
        {

        }


        /**
         * Create insecure channel credentials
         * @return null
         */
        public static function createInsecure()
        {

        }
    }

    class Timeval
    {
        /**
         * Timeval constructor.
         * @param int $microseconds
         */
        public function __construct($microseconds)
        {
        }

        /**
         * Adds another Timeval to this one and returns the sum. Calculations saturate
         * at infinities.
         * @param Timeval $other The other Timeval object to add
         * @return Timeval A new Timeval object containing the sum
         */
        public static function add($other)
        {

        }

        /**
         * Return negative, 0, or positive according to whether a < b, a == b,
         * or a > b respectively.
         * @param Timeval $a The first time to compare
         * @param Timeval $b The second time to compare
         * @return int
         */
        public static function compare($a, $b)
        {

        }

        /**
         * Returns the infinite future time value as a timeval object
         *
         * @return Timeval Infinite future time value
         */
        public static function infFuture()
        {

        }

        /**
         * Returns the infinite past time value as a timeval object
         *
         * @return Timeval
         */
        public static function infPast()
        {

        }

        /**
         * Returns the current time as a timeval object
         * @return Timeval The current time
         */
        public static function now()
        {

        }

        /**
         * Checks whether the two times are within $threshold of each other
         *
         * @param Timeval $a The first time to compare
         * @param Timeval $b The second time to compare
         * @param Timeval $thresh The threshold to check against
         * @return bool True if $a and $b are within $threshold, False otherwise
         */
        public static function similar($a, $b, $thresh)
        {

        }

        /**
         * Sleep until this time, interpreted as an absolute timeout
         * @return void
         */
        public static function sleepUntil()
        {

        }

        /**
         * Subtracts another Timeval from this one and returns the difference.
         * Calculations saturate at infinities.
         *
         * @param Timeval $other The other Timeval object to subtract
         * @return Timeval A new Timeval object containing the diff
         */
        public static function subtract($other)
        {

        }

        /**
         * Returns the zero time interval as a timeval object
         * @return Timeval Zero length time interval
         */
        public static function zero()
        {

        }

    }
}