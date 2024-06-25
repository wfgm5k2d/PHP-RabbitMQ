<?php

use Bunny\Channel;
use Bunny\Client;
use Bunny\Exception\BunnyException;
use Bunny\Message;
use Classes\Strategy\Sender;

/**
 * @var Client $bunny
 * @var Channel $channel
 */
$documentRoot = '/var/www';

if (count($argv) >= 2) {
    $documentRoot = $argv[1];
    $_ENV['DOCUMENT_ROOT'] = $documentRoot;
}

require_once $documentRoot . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable($_ENV['DOCUMENT_ROOT']);
$dotenv->load();

spl_autoload_register(function ($class) use ($documentRoot) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR,  "$documentRoot/public/$class").'.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
});

$bunny = new Client([
    'host' => 'rabbitmq', // Имя контейнера
    'vhost' => '/',
    'user' => $_ENV['RABBITMQ_DEFAULT_USER'],
    'password' => $_ENV['RABBITMQ_DEFAULT_PASS']
]);

try {
    $bunny->connect();
} catch (Exception $e) {
    throw new BunnyException($e->getMessage());
}

$channel = $bunny->channel();

$channel->qos(prefetchCount: 1);

$channel->consume(function (Message $message, Channel $channel) use ($documentRoot) {
    try {
        $sender = new Sender($message->content);
        echo $sender->run();

        $channel->ack($message);
    } catch (\Throwable $t) {
        file_put_contents(
            $documentRoot . '/error_log.log',
            var_export([
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString(),
            ], true),
            FILE_APPEND
        );
        $retryCount = $message->headers['retryCount'] ?? 1;
        if ($retryCount >= 5) {
            $channel->nack($message, requeue: false);
        } else {
            $channel->ack($message);
            $channel->publish($message->content, ['retryCount' => $retryCount + 1] + $message->headers, exchange: 'notifications-service', routingKey: 'delay.6000');
        }
    }
}, 'events.notifications-service');

$bunny->run();