<?php
final class Mail {
	protected $to;
	protected $from;
	protected $mailfrom = '';
	protected $sender;
	protected $subject;
	protected $text;
	protected $html;
	protected $attachments = array();
	public $protocol = 'mail';
	public $hostname;
	public $username;
	public $password;
	public $port = 25;
	public $timeout = 5;
	public $newline = "\n";
	public $crlf = "\r\n";
	public $verp = FALSE;
	public $parameter = '';

	public function setTo($to) {
		$this->to = $to;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setMailFrom($email) {
		$this->mailfrom = $email;
	}

	public function addheader($header, $value) {
		$this->headers[$header] = $value;
	}

	public function setSender($sender) {
		$this->sender = html_entity_decode($sender);
	}

	public function setSubject($subject) {
		$this->subject = html_entity_decode($subject);
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function setHtml($html) {
		$this->html = $html;
	}

	public function addAttachment($file, $filename = '') {
		if (!$filename) {
			$filename = basename($file);
		}

		$this->attachments[] = array(
			'filename' => $filename,
			'file'     => $file
		);
	}

	public function send() {
		if (!$this->to) {
			exit('Error: E-Mail to required!');
		}

		if (!$this->from) {
			exit('Error: E-Mail from required!');
		}

		if (!$this->sender) {
			exit('Error: E-Mail sender required!');
		}

		if (!$this->subject) {
			exit('Error: E-Mail subject required!');
		}

		if ((!$this->text) && (!$this->html)) {
			exit('Error: E-Mail message required!');
		}

		if (is_array($this->to)) {
			$to = implode(',', $this->to);
		} else {
			$to = $this->to;
		}

		$boundary = '----=_NextPart_' . md5(time());

		$header = '';

		if ($this->protocol != 'mail') {
			$header .= 'To: ' . $to . $this->newline;
			$header .= 'Subject: ' . '=?utf-8?B?'.base64_encode($this->subject).'?=' . $this->newline;
		}

		$header .= 'Date: ' . date("D, d M Y H:i:s O") . $this->newline;
		$header .= 'From: ' . '=?UTF-8?B?'.base64_encode($this->sender).'?=' . '<' . $this->from . '>' . $this->newline;
		$header .= 'Reply-To: ' . '=?utf-8?B?'.base64_encode($this->sender).'?=' . '<' . $this->from . '>' . $this->newline;
		$header .= 'Return-Path: ' . $this->from . $this->newline;
		$header .= 'X-Mailer: PHP/' . phpversion() . $this->newline;
		$header .= 'X-ocStore-Site: ' . getenv('SERVER_NAME') . $this->newline;
		$header .= 'MIME-Version: 1.0' . $this->newline;
		$header .= 'Content-Type: multipart/related; boundary="' . $boundary . '";' . $this->newline;

		if (!$this->html) {
			$header .= "\t" . 'type="text/plain"';
			$message  = '--' . $boundary . $this->newline;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . $this->newline;
			$message .= 'Content-Transfer-Encoding: 8bit' . $this->newline . $this->newline;
			$message .= $this->text . $this->newline;
		} else {
			$header .= "\t" . 'type="multipart/alternative"';
			$message  = '--' . $boundary . $this->newline;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $this->newline . $this->newline;
			$message .= '--' . $boundary . '_alt' . $this->newline;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . $this->newline;
			$message .= 'Content-Transfer-Encoding: 8bit' . $this->newline . $this->newline;

			if ($this->text) {
				$message .= $this->text . $this->newline;
			} else {
				$message .= 'This is a HTML email and your email client software does not support HTML email!' . $this->newline;
				$message .= $this->newline . 'Это письмо написано в HTML-формате, но ваша программа не поддерживает отображение' . $this->newline; 
				$message .= 'таких писем. Включите поддержку HTML в вашей почтовой программе или замените программу.' . $this->newline;
			}

			$message .= '--' . $boundary . '_alt' . $this->newline;
			$message .= 'Content-Type: text/html; charset="utf-8"' . $this->newline;
			$message .= 'Content-Transfer-Encoding: 8bit' . $this->newline . $this->newline;
			$message .= $this->html . $this->newline;
			$message .= '--' . $boundary . '_alt--' . $this->newline;
		}

		foreach ($this->attachments as $attachment) {
			if (file_exists($attachment['file'])) {
				$handle = fopen($attachment['file'], 'r');
				$content = fread($handle, filesize($attachment['file']));

				fclose($handle);

				$message .= '--' . $boundary . $this->newline;
				$message .= 'Content-Type: ' . mime_content_type($attachment['file']) . '; name="' . $attachment['filename'] . '"' . $this->newline;
				$message .= 'Content-Transfer-Encoding: base64' . $this->newline;
				$message .= 'Content-Disposition: inline; filename="' . $attachment['filename'] . '"' . $this->newline;
				$message .= 'Content-ID: <' . $attachment['filename'] . '>' . $this->newline . $this->newline;
				$message .= chunk_split(base64_encode($content));
			}
		}

		$message .= '--' . $boundary . '--' . $this->newline;

		if ($this->protocol == 'mail') {
			ini_set('sendmail_from', $this->from);

			if ($this->parameter) {
				mail($to, '=?UTF-8?B?'.base64_encode($this->subject).'?=', $message, $header, $this->parameter);
			} else {
				mail($to, '=?UTF-8?B?'.base64_encode($this->subject).'?=', $message, $header);
			}

		} elseif ($this->protocol == 'smtp') {
			$handle = fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout);

			if (!$handle) {
				error_log('Error: ' . $errstr . ' (' . $errno . ')');
			} else {
				if (substr(PHP_OS, 0, 3) != 'WIN') {
					socket_set_timeout($handle, $this->timeout, 0);
				}

				while ($line = fgets($handle, 515)) {
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

# абсолютно нерабочий код, т.к. имя хоста с префиксом 'tls://' включает соответствующее шифрование с самого начала,
# тогда как команда STARTTLS сама по себе не инициализирует начало зашифрованной сессии
#				if (substr($this->hostname, 0, 3) == 'tls') {
#					fputs($handle, 'STARTTLS' . $this->crlf);
#
#					while ($line = fgets($handle, 515)) {
#						$reply .= $line;
#
#						if (substr($line, 3, 1) == ' ') {
#							break;
#						}
#					}
#
#					if (substr($reply, 0, 3) != 220) {
#						error_log('Error: STARTTLS not accepted from server!');
#					}
#				}

				if (!empty($this->username)  && !empty($this->password)) {
					$starttls = false;
					fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . $this->crlf);

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 4, 8) == 'STARTTLS') {
							$starttls = true;
						}
						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 250) {
						error_log('Error: EHLO not accepted from server! (' . $reply . ')');
					}

					//включить TLS-шифрование, если сервер поддерживает STARTTLS
					if ($starttls and (substr($this->hostname, 0, 6) != 'ssl://') and
										(substr($this->hostname, 0, 6) != 'tls://') and
										fputs($handle, 'STARTTLS' . $this->crlf))
					{
						$reply = '';
						while ($line = fgets($handle, 515)) {
							$reply .= $line;
							if (substr($line, 3, 1) == ' ') {
								break;
							}
						}
						if (substr($reply, 0, 3) == 220) {
							if ( !stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) ) {
								error_log('Error: TLS handshake failure, connection not encrypted!');
							}
						} else {
							error_log('Error: STARTTLS not accepted from server! (' . $reply . ')');
						}
					}

					fputs($handle, 'AUTH LOGIN' . $this->crlf);

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 334) {
						error_log('Error: AUTH LOGIN not accepted from server! (' . $reply . ')');
					}

					fputs($handle, base64_encode($this->username) . $this->crlf);

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 334) {
						error_log('Error: Username not accepted from server! (' . $reply . ')');
					}

					fputs($handle, base64_encode($this->password) . $this->crlf);

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 235) {
						error_log('Error: Password not accepted from server! (' . $reply . ')');
					}
				} else {
					fputs($handle, 'HELO ' . getenv('SERVER_NAME') . $this->crlf);

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 250) {
						error_log('Error: HELO not accepted from server! (' . $reply . ')');
					}
				}

				$this->mailfrom = $this->mailfrom ? $this->mailfrom : $this->from;
				if ($this->verp) {
					fputs($handle, 'MAIL FROM: <' . $this->mailfrom . '>XVERP' . $this->crlf);
				} else {
					fputs($handle, 'MAIL FROM: <' . $this->mailfrom . '>' . $this->crlf);
				}

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
					error_log('Error: MAIL FROM not accepted from server! (' . $reply . ')');
				}

				if (!is_array($this->to)) {
					fputs($handle, 'RCPT TO: <' . $this->to . '>' . $this->crlf);

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
						error_log('Error: RCPT TO not accepted from server! (' . $reply . ')');
					}
				} else {
					foreach ($this->to as $recipient) {
						fputs($handle, 'RCPT TO: <' . $recipient . '>' . $this->crlf);

						$reply = '';

						while ($line = fgets($handle, 515)) {
							$reply .= $line;

							if (substr($line, 3, 1) == ' ') {
								break;
							}
						}

						if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
							error_log('Error: RCPT TO not accepted from server! (' . $reply . ')');
						}
					}
				}

				fputs($handle, 'DATA' . $this->crlf);

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 354) {
					error_log('Error: DATA not accepted from server! (' . $reply . ')');
				}

				fputs($handle, $header . $this->newline . $this->newline . $message . $this->crlf);
				fputs($handle, '.' . $this->crlf);

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
					error_log('Error: DATA not accepted from server! (' . $reply . ')');
				}

				fputs($handle, 'QUIT' . $this->crlf);

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 221) {
					error_log('Error: QUIT not accepted from server! (' . $reply . ')');
				}

				fclose($handle);
			}
		}
	}
}
?>