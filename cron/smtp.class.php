<?php

class SMTP {

	private $smtp_host = null;
	private $smtp_port = null;

	private $smtp_email = null;
	private $smtp_from = null;

	private $smtp_auth = null;
	private $smtp_username = null;
	private $smtp_password = null;

	private $smtp_charset = null;

	private $_debug = null;
	private $_socket = null;

	function __construct($config)
	{
		$this->_debug = false;
		$this->smtp_charset = 'UTF-8';
		$this->smtp_host = 'localhost';
		$this->smtp_port = 25;
		$this->smtp_auth = true;

		if(is_array($config) && $config) {
			foreach($config as $k=>$v) $this->set($k, $v);
		}
	}

	function set($var, $value)
	{
		$v = $this->get($var);
		$this->$var = $value;
		return $v;
	}

	function get($var)
	{
		if(isset($this->$var)) return $this->$var;
		return null;
	}

	function send($mail_to, $subject, $message, $headers='') 
	{
		$this->smtp_to = $mail_to;

        	$body =   "Date: ".date("D, d M Y H:i:s") . " UT\r\n";
        	$body .=   "Subject: =?".$this->smtp_charset."?B?".base64_encode($subject)."=?=\r\n";
        	if ($headers) $body .= $headers."\r\n\r\n";
        	else
        	{
                	$body .= "Reply-To: ".$this->smtp_email."\r\n";
                	$body .= "MIME-Version: 1.0\r\n";
                	$body .= "Content-Type: text/plain; charset=\"".$this->smtp_charset."\"\r\n";
                	$body .= "Content-Transfer-Encoding: 8bit\r\n";
                	$body .= "From: =?".$this->smtp_charset."?B?".base64_encode($this->smtp_from)."?= <".$this->smtp_email.">\r\n";
                	$body .= "To: {$this->smtp_to} <{$this->smtp_to}>\r\n";
                	$body .= "X-Priority: 3\r\n\r\n";
        	}

        	$body .=  $message."\r\n";

		$result = $this->_transport($body);
		$this->log[] = array(
			'body'		=>	$body,
			'message' 	=> 	$message,
			'subject' 	=> 	$subject,
			'recepient'	=>	$mail_to,
			'timestamp'	=>	time(),
			'result' 	=>	$result
		);
		return $result;
	}

	private function _transport($body)
	{
		if( !$this->_socket = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30) ) {
			if ($this->_debug) $this->debug("Connecting error: {$errno} - {$errstr}");
			return false;
		}
		if (!$this->_response("220", __LINE__)) return false;

		fputs($this->_socket, "HELO {$this->smtp_host}\r\n");
		if (!$this->_response("250", __LINE__)) {
			if ($this->_debug) $this->debug("HELO command error");
			fclose($this->_socket);
			return false;
		}

		if($this->smtp_auth) {
			fputs($this->_socket, "AUTH LOGIN\r\n");
			if (!$this->_response("334", __LINE__)) {
				if ($this->_debug) $this->debug("AUTH LOGIN response error");
				fclose($this->_socket);
				return false;
			}

			fputs($this->_socket, base64_encode($this->smtp_username) . "\r\n");
			if (!$this->_response("334", __LINE__)) {
				if ($this->_debug) $this->debug("Login incorrect");
				fclose($this->_socket);
				return false;
			}

			fputs($this->_socket, base64_encode($this->smtp_password) . "\r\n");
			if (!$this->_response("235", __LINE__)) {
				if ($this->_debug) $this->debug("Password incorrect");
				fclose($this->_socket);
				return false;
			}
		}

		fputs($this->_socket, "MAIL FROM: <{$this->smtp_email}>\r\n");
		if (!$this->_response("250", __LINE__)) {
			if ($this->_debug) $this->debug("MAIL FROM command error");
			fclose($this->_socket);
			return false;
		}

		fputs($this->_socket, "RCPT TO: <{$this->smtp_to}>\r\n");
		if (!$this->_response("250", __LINE__)) {
			if ($this->_debug) $this->debug("RCPT TO command error");
			fclose($this->_socket);
			return false;
		}

		fputs($this->_socket, "DATA\r\n");
		if (!$this->_response("354", __LINE__)) {
			if ($this->_debug) $this->debug("DATA command error");
			fclose($this->_socket);
			return false;
		}

		fputs($this->_socket, $body."\r\n.\r\n");
		if (!$this->_response("250", __LINE__)) {
			if ($this->_debug) $this->debug("Mail deliver error");
			fclose($this->_socket);
			return false;
		}

		fputs($this->_socket, "QUIT\r\n");
		fclose($this->_socket);
		return true;
	}

	private function _response($code, $line = __LINE__) 
	{
		$response = null;
		while (substr($response, 3, 1) != ' ') {
			if (!($response = fgets($this->_socket, 256))) {
				if ($this->_debug) $this->debug("Unexpected response, given response {$response} in line {$line}");
				return false;
			}
		}

		if (!(substr($response, 0, 3) == $code)) {
			if ($this->_debug) $this->debug("Expected response {$code}, given response {$response} in line {$line}");
			return false;
		}
		return true;
	}

	function debug($msg)
	{
		echo $msg."\r\n";
	}

}