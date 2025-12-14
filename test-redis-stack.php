<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Redis Stack JSON Module ===\n\n";

try {
    echo "1. Getting Redis client...\n";
    $redis = app('redis')->connection()->client();
    echo "   ✓ Client connected\n\n";

    echo "2. Testing basic Redis command (PING)...\n";
    $pong = $redis->ping();
    echo "   ✓ PING response: " . $pong . "\n\n";

    echo "3. Testing JSON.SET command...\n";
    $testData = json_encode(['name' => 'test', 'value' => 123]);
    echo "   Data: " . $testData . "\n";

    $result = $redis->rawCommand('JSON.SET', 'test:key', '$', $testData);
    echo "   ✓ JSON.SET result: " . var_export($result, true) . "\n\n";

    echo "4. Testing JSON.GET command...\n";
    $retrieved = $redis->rawCommand('JSON.GET', 'test:key');
    echo "   ✓ JSON.GET result: " . $retrieved . "\n\n";

    echo "5. Cleaning up...\n";
    $redis->del('test:key');
    echo "   ✓ Test key deleted\n\n";

    echo "=== All tests passed! ===\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
}
