<?php

namespace Classes\Strategy;

use Classes\Exceptions\SlackException;

class SlackStrategy implements ISender
{
    public function __construct(public string $body)
    {}

    /**
     * @throws SlackException
     */
    public function send()
    {
        $writeLog = file_put_contents(
            $_ENV['DOCUMENT_ROOT'] . '/slack.log',
            date('d-m-Y H:i:s') . ': Отправили сообщение в Slack: ' . PHP_EOL . $this->body . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        return $writeLog ? 'По дефолту шлем сообщения в Slack' . PHP_EOL : throw new SlackException('Ошибка при отправке сообщения');
    }


    public static function chanel(): string
    {
        return 'slack';
    }
}