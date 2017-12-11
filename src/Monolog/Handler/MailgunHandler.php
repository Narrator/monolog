<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * MailgunHandler uses the Mailgun API v3 to send Log emails, more information in https://documentation.mailgun.com/en/latest/api_reference.html
 *
 * @author Kaushik Gnanaskandan <kaushik.subman@gmail.com>
 */
class MailgunHandler extends MailHandler
{
    /**
     * The Mailgun API Key
     * @var string
     */
    protected $apiKey;

    /**
     * The Mailgun Domain
     * @var string
     */
    protected $domain;

    /**
     * The email addresses from which the message will be sent
     * @var string
     */
    protected $from;

    /**
     * The email addresses to which the message will be sent
     * @var array
     */
    protected $to;

    /**
     * The subject of the email
     * @var string
     */
    protected $subject;

    /**
     * @param string       $apiKey  The Mailgun API Key
     * @param string       $domain  The Mailgun Domain
     * @param string       $from    The sender of the email
     * @param string|array $to      The recipients of the email
     * @param string       $subject The subject of the mail
     * @param int          $level   The minimum logging level at which this handler will be triggered
     * @param bool         $bubble  Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(string $apiKey, string $domain, string $from, $to, string $subject, int $level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->apiKey = $apiKey;
        $this->domain = $domain;
        $this->from = $from;
        $this->to = (array) $to;
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    protected function send(string $content, array $records)
    {
        $message = [];
        $message['from'] = $this->from;
        $message['to'] = rtrim(implode(',', $this->to), ',');
        $message['subject'] = $this->subject;

        if ($this->isHtmlBody($content)) {
            $message['html'] = $content;
        } else {
            $message['text'] = $content;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/$this->domain/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:$this->apiKey');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($message));
        Curl\Util::execute($ch, 2);
    }
}
