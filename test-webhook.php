<?php

// Test webhook payload simulation
$testPayload = [
    'event-data' => [
        'event' => 'opened',
        'timestamp' => time(),
        'recipient' => 'test@example.com',
        'message' => [
            'headers' => [
                'message-id' => 'test-message-' . uniqid()
            ]
        ],
        'user-agent' => 'Test User Agent',
        'ip' => '192.168.1.1'
    ]
];

// Send POST request to your local webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/webhooks/mailgun');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testPayload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n";