<?php
use Bunny\Channel;

/**
 * @var Channel $channel
 */

// Сервис рассылок
$channel->exchangeDeclare('notifications-service', durable: true);

$channel->queueDeclare('notifications-service.failed', durable: true);
$channel->queueBind('notifications-service.failed', 'notifications-service', 'failed');

$channel->queueDeclare('events.notifications-service', durable: true, arguments: [
    'x-dead-letter-exchange' => 'notifications-service',
    'x-dead-letter-routing-key' => 'failed',
]);
$channel->queueBind('events.notifications-service', 'events', 'payment_succeeded');
$channel->queueBind('events.notifications-service', 'notifications-service', 'events.notifications-service');

$channel->queueDeclare('notifications-service.delayed.6000', durable: true, arguments: [
    'x-dead-letter-exchange' => 'notifications-service',
    'x-dead-letter-routing-key' => 'events.notifications-service',
    'x-message-ttl' => 6000,
]);
$channel->queueBind('notifications-service.delayed.6000', 'notifications-service', 'delay.6000');