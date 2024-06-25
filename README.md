# PHP-RabbitMQ
## Pet-проект для демонстрации возможностей RabbitMQ в качестве посредника для отправки уведомлений

[![P|Piratecode](https://piratecode.ru/img/elements/header/_logo.svg)](https://piratecode.ru)

## Информация
Проект реализует сервис рассылок уведомлений на `PHP + RabbitMQ`. Приложение слушает очередь `RabbitMQ`, рассылает уведомления по разным каналам - `Email, SMS, Telegram, Slack и т.д.`
Реальных рассылок не происходит, все приходящие в него данные пишутся в `*.log` файлы. Название файла зависит от канала в который пришло сообщение.
Если вы хотите расширить или дописать логику рассылки, классы лежат по пути
`publuc/Classes/Strategy`
`publuc/Classes/Exceptions`
Логика отправки реализуется в методе `send()`
```php
    public function send()
    {
        // Ваш код отправки данных
    }
```
Если хотите реализовать свой канал, создайте класс имплементируя интерфейс Isender
```php
<?php

namespace Classes\Strategy;

use Classes\Exceptions\TelegramException;

class MyCustomClass implements ISender
{}
```
и добавьте вызов в стратегию внутри класса `Sender.php:40`
```php
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
```
## Общий принцип работы
- Публикуется запись в RabbitMQ
- Монитор забирает 1 запись из кролика и пытается отправить её через нужный канал
- Если все хорошо, то просто акаем (`->ack()`) сообщение

Если произошла ошибка на каком-то шаге
- Пишется лог с ошибкой
- Проверяется параметр `retryCount` и если он меньше 5 (попыток) то текущее сообщение акается и дублируется в новое, но уже в очередь `notifications-service.delayed.6000` в которой томится 6 секунд и повторно идет на отправку
- Если по истечении 5 попыток сообщение так и не удалось отправить, накаем (`->nack()`) его.

## Установка

В вашей системе должен присутствовать Docker
Для Linux (Ubuntu)
```sh
sudo apt install docker docker-compose
```
Скопируйте `.env.example` в `.env` и установите переменные при желании. По умолчанию они уже установлены
```sh
# Логин пользователя для RabbitMQ
RABBITMQ_DEFAULT_USER=guest
# Пароль пользователя для RabbitMQ
RABBITMQ_DEFAULT_PASS=guest
```
Если докер уже установлен в системе, сбилдить проект
```bash
docker-compose build app
```
Когда билд закончится, запустите сервер
```bash
docker-compose up -d
```
Зайдите в контейнер `php-app` и установите зависимости `composer`
```bash
docker exec -it php-app bash
composer install
```

## Запуск

Перейдите в [RabbitMQ](http://localhost:15673/#/exchanges) ([http://localhost:15673/#/exchanges](http://localhost:15673/#/exchanges))
Логин и пароль должны быть как в файле `.env`

#### Отправить первое сообщение
Для отправки вы можете использовать `Postman`
Отправьте `POST` запрос по адресу [http://localhost:8000](http://localhost:8000) с телом типа json
```json
{
    "chanel": "mail",
    "body": "Hello World!"
}
```
Автоматически будут задекларированы обменники `events` и `notifications-service`
Внутри контейнера докер `php-app` запустите монитор очереди передав параметром корневой путь до проекта
```bash
php public/monitor /var/www
```
Вывод будет такой:
```
Отправили сообщение на почту
```
Потому что использовали канал mail
В корне проекта будет создан файл `mail.log` с вашим содержимым body

## Массовое тестирование
Возможно вам захочется посмотреть в действии как работают очереди
Вначале запустите монитор
```bash
php public/monitor.php /var/www
```
Затем запустите файл `public/test.php` передав параметром корневой путь до проекта
```bash
php public/test.php /var/www
```
Сгенерируется `100 000` (сто тысяч) очередей. Можете менять это значение

| !ВАЖНО!  В классе `\Classes\Strategy\TelegramStrategy` код обрабатывается с ошибкой в 50% случаев. Это для того, чтобы вы смогли протестировать faild ситуации в очереди |
|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|