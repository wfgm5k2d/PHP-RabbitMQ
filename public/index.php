<?php

use Bunny\Channel;
use Bunny\Client;

/**
 * @var Client $bunny
 * @var Channel $channel
 */

$post = file_get_contents('php://input');

if (!json_validate($post)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Проверьте правильность json'
    ]);

    return false;
}

if ($data = json_decode($post, true)) {
    $missingParameters = [];

    if (!array_key_exists('chanel', $data)) {
        $missingParameters[] = 'Parameter: chanel is required';
    }

    if (!array_key_exists('body', $data)) {
        $missingParameters[] = 'Parameter: body is required';
    }

    if (!empty($missingParameters)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => $missingParameters
        ]);

        return false;
    }
}

$channel->publish($post, exchange: 'events', routingKey: 'payment_succeeded');