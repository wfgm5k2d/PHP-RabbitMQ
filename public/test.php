<?php
use Bunny\Channel;
use Bunny\Client;
use Bunny\Exception\BunnyException;

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

$availableChannels = ['mail', 'sms', 'telegram', 'slack'];
$counter = 0;

while ($counter < 100000) {
    $counter++;

    $key = mt_rand(0, 3);
    $body = json_encode([
        'chanel' => $availableChannels[$key],
        'body' => "Это сообщение предназначено для канала $availableChannels[$key]"
    ]);

    echo "Это сообщение предназначено для канала $availableChannels[$key]" . PHP_EOL;

    $channel->publish($body, exchange: 'events', routingKey: 'payment_succeeded');
}
