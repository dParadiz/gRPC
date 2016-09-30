#Overview

PHP wrapper code for gRPC extension. Including decorator for Grpc\Call and some basic ClientStub implementation.
More about gRPC can be found on [grpc.io](http://www.grpc.io/).

#Environment

* Requires PHP 5.5 or higher
* Requires gRPC php extension to be installed. 

Quick install of  gRPC php extension

```sh
sudo pecl install grpc
```

#Usage example

In this example we will use [Protobuf for PHP](https://github.com/protobuf-php/protobuf).
package, that can be installed via `composer`

```sh
composer require "protobuf-php/protobuf"
```

With this package we will compile protocol buffers from `helloword.proto` file with following content
 
```proto
syntax = "proto2";
option java_package = "ex.grpc";
package Helloworld;

// The greeting service definition.
service Greeter {
  // Sends a greeting
  rpc SayHello (HelloRequest) returns (HelloReply) {}
}

// The request message containing the user's name.
message HelloRequest {
  optional string name = 1;
}

// The response message containing the greetings
message HelloReply {
  optional string message = 1;
}
```
by following command

```sh
php ./vendor/bin/protobuf --include-descriptors -i . -o ./example/ ./helloword.proto
```

This will generate flowing folder structure
```console
example/
└── Helloworld   
    ├── Greeter.php
    ├── HelloReply.php
    └── HelloRequest.php
```

Now we need to create `Helloworld\ClientStub` implementing `Helloworld\Greeter` interface and extending 
`Grpc\ClientStub`.

```php
namespace Helloworld;

use Grpc;
use Protobuf;

class ClientStub extends Grpc\ClientStub implements Greeter
{
    /**
     * @param HelloRequest $input
     * @return HelloReply
     */
    public function sayHello(HelloRequest $input)
    {        
        $call = new Grpc\Call($this->channel, '/Helloworld.Greeter/SayHello', Grpc\Timeval::infFuture());
        $streamCall = new Grpc\StreamingCall($call);

        $serializer = new Protobuf\MessageSerializer();
        
        $response = $streamCall->start()->write((string)$serializer->serialize($input))->readAndClose();

        return $serializer->unserialize(HelloReply::class, $response);
    }
}
```

ClientStub is responsible for preparing right data for the call and returning the correct type. Now we can call
the client 

```php
$channel = new Channel('localhost:50051', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure(),
]);

$client = new Helloworld\ClientStub($channel);

$message = new Helloworld\HelloRequest();
$message->setName('My Name');

$replay = $client->sayHello($message);

echo $replay->getMessage() . PHP_EOL;
```

Here we assume that server is running on localhost and port 50051. More how to start server
can be found [https://github.com/grpc/grpc/tree/master/examples](https://github.com/grpc/grpc/tree/master/examples)







