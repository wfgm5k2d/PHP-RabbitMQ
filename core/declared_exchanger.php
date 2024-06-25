<?php
use Bunny\Channel;

/**
 * @var Channel $channel
 */

// Декларируем обменник
$channel->exchangeDeclare('events', durable: true);