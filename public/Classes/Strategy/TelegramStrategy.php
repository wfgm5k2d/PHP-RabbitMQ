<?php

namespace Classes\Strategy;

use Classes\Exceptions\TelegramException;

class TelegramStrategy implements ISender
{
    public function __construct(public string $body)
    {}

    /**
     * @throws TelegramException
     */
    public function send()
    {
        if (mt_rand(0, 1)) {
            throw new TelegramException('Специально поломал программу для Telegram отправителя чтобы проверить работу ошибок');
        }

        $writeLog = file_put_contents(
            $_ENV['DOCUMENT_ROOT'] . '/telegram.log',
            date('d-m-Y H:i:s') . ': Отправили сообщение в telegram: ' . PHP_EOL . $this->body . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        return $writeLog ? 'Отправили сообщение в telegram' . PHP_EOL : throw new TelegramException('Ошибка при отправке сообщения');
    }

    public static function chanel(): string
    {
        return 'telegram';
    }
}