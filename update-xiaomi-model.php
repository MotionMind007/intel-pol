<?php

require __DIR__ . '/backend/vendor/autoload.php';

$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Updating Xiaomi Model Configuration ===" . PHP_EOL . PHP_EOL;

$provider = App\Models\AiProvider::first();
$model = App\Models\AiModel::first();

echo "Testing Xiaomi models..." . PHP_EOL;

$client = new \GuzzleHttp\Client(['timeout' => 15]);
$apiKey = $provider->api_key_encrypted;
$baseUrl = $provider->base_url;

// Test mimo-v2.5-pro (best model for complex tasks)
$testModel = 'mimo-v2.5-pro';

echo "Testing: {$testModel}... ";

try {
    $response = $client->post($baseUrl . '/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => $testModel,
            'messages' => [
                ['role' => 'user', 'content' => 'Say OK']
            ],
            'max_tokens' => 10,
        ],
    ]);
    
    $data = json_decode($response->getBody(), true);
    $reply = $data['choices'][0]['message']['content'] ?? 'No response';
    
    echo "✓ SUCCESS!" . PHP_EOL;
    echo "  Response: {$reply}" . PHP_EOL;
    echo PHP_EOL;
    
    // Update model in database
    echo "Updating model in database..." . PHP_EOL;
    $model->update([
        'model_name' => $testModel,
        'display_name' => 'Xiaomi Mimo v2.5 Pro',
        'context_window' => 128000,
    ]);
    
    echo "✓ Model updated successfully!" . PHP_EOL;
    echo PHP_EOL;
    
    echo "Current Configuration:" . PHP_EOL;
    echo "  Provider: {$provider->name}" . PHP_EOL;
    echo "  Base URL: {$provider->base_url}" . PHP_EOL;
    echo "  Model: {$model->model_name}" . PHP_EOL;
    echo "  Display Name: {$model->display_name}" . PHP_EOL;
    echo PHP_EOL;
    
    echo "✅ Xiaomi API is ready to use!" . PHP_EOL;
    echo "   You can now test screening with real AI." . PHP_EOL;
    
} catch (Exception $e) {
    echo "✗ FAILED!" . PHP_EOL;
    echo "  Error: " . $e->getMessage() . PHP_EOL;
    
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        $body = $e->getResponse()->getBody()->getContents();
        echo "  Response: {$body}" . PHP_EOL;
    }
}
