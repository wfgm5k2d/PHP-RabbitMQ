<?php
use Bunny\Client;

$bunny = new Client([
    'host'      => 'rabbitmq', // Имя контейнера
    'vhost'     => '/',
    'user'      => $_ENV['RABBITMQ_DEFAULT_USER'],
    'password'  => $_ENV['RABBITMQ_DEFAULT_PASS'],
]);

$bunny->connect();
$channel = $bunny->channel();