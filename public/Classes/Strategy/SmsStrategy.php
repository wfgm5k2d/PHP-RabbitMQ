<?php

namespace Classes\Strategy;

use Classes\Exceptions\SmsException;

class SmsStrategy implements ISender
{
    public function __construct(public string $body)
    {}

    /**
     * @throws SmsException
     */
    public function send()
    {
        $writeLog = file_put_contents(
            $_ENV['DOCUMENT_ROOT'] . '/sms.log',
            date('d-m-Y H:i:s') . ': Отправили сообщение на телефон: ' . PHP_EOL . $this->body . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        return $writeLog ? 'Отправили сообщение на телефон' . PHP_EOL : throw new SmsException('Ошибка при отправке сообщения');
    }

    public static function chanel(): string
    {
        return 'sms';
    }
}