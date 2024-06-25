<?php

namespace Classes\Strategy;

interface ISender
{
    /**
     * Основной метод отправки в нужный канал
     * @return mixed
     */
    public function send();

    /**
     * Должен вернуть код названия канала
     * @return string
     */
    public static function chanel(): string;
}