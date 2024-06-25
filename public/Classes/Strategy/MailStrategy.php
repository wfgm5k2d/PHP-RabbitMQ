<?php

namespace Classes\Strategy;

use Classes\Exceptions\MailException;

class MailStrategy implements ISender
{
    public function __construct(public string $body)
    {}

    /**
     * @throws MailException
     */
    public function send()
    {
        $writeLog = file_put_contents(
            $_ENV['DOCUMENT_ROOT'] . '/mail.log',
            date('d-m-Y H:i:s') . ': Отправили сообщение на почту: ' . PHP_EOL . $this->body . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        return $writeLog ? 'Отправили сообщение на почту' . PHP_EOL : throw new MailException('Ошибка при отправке сообщения');
    }

    public static function chanel(): string
    {
        return 'mail';
    }
}