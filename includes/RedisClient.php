<?php
class RedisClient {
    private $socket;

    public function __construct($host = '127.0.0.1', $port = 6379, $timeout = 2.5) {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if (!$this->socket) {
            throw new Exception("Could not connect to Redis at $host:$port - $errstr ($errno)");
        }
    }


    public function publish($channel, $message) {

        $command = "*3\r\n" .
                   "$" . strlen("PUBLISH") . "\r\nPUBLISH\r\n" .
                   "$" . strlen($channel) . "\r\n$channel\r\n" .
                   "$" . strlen($message) . "\r\n$message\r\n";

        fwrite($this->socket, $command);
        
        $response = fgets($this->socket);
        return $response;
    }

    public function __destruct() {
        if ($this->socket) {
            fclose($this->socket);
        }
    }
}