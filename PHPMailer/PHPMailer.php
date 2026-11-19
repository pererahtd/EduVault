<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $Priority;
    public $CharSet = 'UTF-8';
    public $ContentType = 'text/html';
    public $Encoding = '8bit';
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $WordWrap = 0;
    public $Mailer = 'smtp';
    public $Sendmail = '/usr/sbin/sendmail';
    public $UseSendmailOptions = true;
    public $ConfirmReadingTo = '';
    public $MessageID = '';
    public $Host = 'smtp.gmail.com';
    public $Port = 587;
    public $Helo = '';
    public $SMTPSecure = 'tls';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = true;
    public $SMTPOptions = [];
    public $Username = '';
    public $Password = '';
    public $AuthType = '';
    public $Timeout = 30;
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';
    public $SMTPKeepAlive = false;
    public $SingleTo = false;

    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $ReplyTo = [];
    protected $smtp;

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = [$address, $name];
        return true;
    }

    public function isHTML($ishtml = true)
    {
        if ($ishtml) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }

    public function send()
    {
        if (!$this->smtp) {
            $this->smtp = new SMTP();
        }
        
        try {
            if (!$this->smtp->connect($this->Host, $this->Port, $this->Timeout)) {
                throw new Exception('SMTP Connection Failed');
            }
            if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS) {
                $this->smtp->startTLS();
            }
            if ($this->SMTPAuth) {
                if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                    throw new Exception('SMTP Authentication Failed');
                }
            }
            $this->smtp->mail($this->From);
            foreach ($this->to as $to) {
                $this->smtp->recipient($to[0]);
            }
            $header = "From: {$this->FromName} <{$this->From}>\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: {$this->ContentType}; charset={$this->CharSet}\r\n";
            $header .= "Subject: {$this->Subject}\r\n";
            
            $this->smtp->data($header . "\r\n" . $this->Body);
            $this->smtp->quit();
            return true;
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            return false;
        }
    }
}