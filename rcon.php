<?php

class MinecraftRcon {
    private $socket;
    private $host;
    private $port;
    private $password;
    private $authenticated = false;

    public function __construct($host, $port, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    public function connect() {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 3);
        if (!$this->socket) {
            throw new Exception("Ошибка подключения: $errstr ($errno)");
        }

        $this->authenticate();
    }

    private function authenticate() {
        $this->sendPacket(0, 3, $this->password);
        $response = $this->readPacket();

        if ($response['type'] === 2) {
            $this->authenticated = true;
        } else {
            throw new Exception("Ошибка аутентификации RCON");
        }
    }

    public function sendCommand($command) {
        if (!$this->authenticated) {
            throw new Exception("Клиент не аутентифицирован");
        }

        $this->sendPacket(0, 2, $command);
        $response = $this->readPacket();

        return $response['body'];
    }

    private function sendPacket($id, $type, $body) {
        $packet = pack('VV', $id, $type) . $body . "\x00\x00";
        $packet = pack('V', strlen($packet)) . $packet;

        fwrite($this->socket, $packet, strlen($packet));
    }

    private function readPacket() {
        $size = fread($this->socket, 4);
        $size = unpack('V', $size)[1];

        $packet = fread($this->socket, $size);
        $parts = unpack('Vid/Vtype/a*body', $packet);

        return $parts;
    }

    public function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
        }
    }
}

