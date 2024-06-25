<?php

namespace Classes\Strategy;

use Classes\Exceptions\MailException;
use Classes\Exceptions\SlackException;
use Classes\Exceptions\SmsException;
use Classes\Exceptions\TelegramException;

class Sender
{
    private string $chanel;
    private string $body;
    private array $jsonData;

    public function __construct(public string $json)
    {
        $this->jsonData = json_decode($this->json, true);
        $this->chanel = $this->jsonData['chanel'];
        $this->body = $this->jsonData['body'];
    }

    /**
     * @throws MailException
     * @throws SlackException
     * @throws SmsException
     * @throws TelegramException
     */
    public function run()
    {
        return $this->selectStrategy();
    }

    /**
     * @throws MailException
     * @throws SlackException
     * @throws SmsException
     * @throws TelegramException
     */
    private function selectStrategy()
    {
        return match ($this->chanel) {
            MailStrategy::chanel() => (new MailStrategy($this->body))->send(),
            SmsStrategy::chanel() => (new SmsStrategy($this->body))->send(),
            TelegramStrategy::chanel() => (new TelegramStrategy($this->body))->send(),
            default => (new SlackStrategy($this->body))->send(),
        };
    }
}