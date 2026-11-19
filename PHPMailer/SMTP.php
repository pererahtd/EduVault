<?php
namespace PHPMailer\PHPMailer;

class SMTP
{
    protected $connection;
    protected $error = [];

    public function connect($host, $port = 587, $timeout = 30)
    {
        $this->connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->connection) {
            return false;
        }
        $response = fgets($this->connection, 512);
        return true;
    }

    public function startTLS()
    {
        fputs($this->connection, "STARTTLS\r\n");
        fgets($this->connection, 512);
        return stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    }

    public function authenticate($username, $password)
    {
        fputs($this->connection, "AUTH LOGIN\r\n");
        fgets($this->connection, 512);
        fputs($this->connection, base64_encode($username) . "\r\n");
        fgets($this->connection, 512);
        fputs($this->connection, base64_encode($password) . "\r\n");
        $response = fgets($this->connection, 512);
        return (substr($response, 0, 3) == '235');
    }

    public function mail($from)
    {
        fputs($this->connection, "MAIL FROM: <$from>\r\n");
        return fgets($this->connection, 512);
    }

    public function recipient($to)
    {
        fputs($this->connection, "RCPT TO: <$to>\r\n");
        return fgets($this->connection, 512);
    }

    public function data($data)
    {
        fputs($this->connection, "DATA\r\n");
        fgets($this->connection, 512);
        fputs($this->connection, $data . "\r\n.\r\n");
        return fgets($this->connection, 512);
    }

    public function quit()
    {
        fputs($this->connection, "QUIT\r\n");
        fclose($this->connection);
    }
}