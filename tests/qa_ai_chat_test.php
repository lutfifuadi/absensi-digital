<?php
// QA Test Script untuk Fitur AI Chat
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$results = [];
$passed = 0;
$failed = 0;

echo "========================================\n";
echo "QA TEST: Fitur AI Chat\n";
echo "========================================\n\n";

// Test 1: GeminiService dapat di-instantiate
try {
    $service = new \App\Services\GeminiService();
    $results[] = ['Test 1 - GeminiService instantiate', 'PASS', 'OK'];
    $passed++;
} catch (\Exception $e) {
    $results[] = ['Test 1 - GeminiService instantiate', 'FAIL', $e->getMessage()];
    $failed++;
}

// Test 2: ChatLog model exists
try {
    $model = new \App\Models\ChatLog();
    $results[] = ['Test 2 - ChatLog model exists', 'PASS', get_class($model)];
    $passed++;
} catch (\Exception $e) {
    $results[] = ['Test 2 - ChatLog model exists', 'FAIL', $e->getMessage()];
    $failed++;
}

// Test 3: ChatLog fillable attributes
$fillable = (new \App\Models\ChatLog())->getFillable();
$expected = ['user_id', 'role', 'message', 'metadata'];
$match = count(array_intersect($expected, $fillable)) === count($expected);
$results[] = ['Test 3 - ChatLog fillable', $match ? 'PASS' : 'FAIL', 'fillable: ' . implode(',', $fillable)];
if ($match) $passed++; else $failed++;

// Test 4: AiChatController exists
$exists = class_exists(\App\Http\Controllers\Admin\AiChatController::class);
$results[] = ['Test 4 - AiChatController exists', $exists ? 'PASS' : 'FAIL', $exists ? 'OK' : 'NOT FOUND'];
if ($exists) $passed++; else $failed++;

// Test 5: GeminiService has required methods
$methods = get_class_methods(\App\Services\GeminiService::class);
$required = ['sendMessage', 'sendWithTools', 'getHistory', 'clearHistory', 'getToolDefinitions'];
$hasMethods = count(array_intersect($required, $methods)) === count($required);
$results[] = ['Test 5 - Required methods exist', $hasMethods ? 'PASS' : 'FAIL', $hasMethods ? 'OK' : 'Missing: ' . implode(',', array_diff($required, $methods))];
if ($hasMethods) $passed++; else $failed++;

// Test 6: GeminiService configuration
$reflection = new ReflectionClass(\App\Services\GeminiService::class);
$timeout = $reflection->getProperty('timeout');
$timeout->setAccessible(true);
$maxRetries = $reflection->getProperty('maxRetries');
$maxRetries->setAccessible(true);
$service = new \App\Services\GeminiService();
$timeoutVal = $timeout->getValue($service);
$retryVal = $maxRetries->getValue($service);
$configOk = $timeoutVal === 60 && $retryVal === 3;
$results[] = ['Test 6 - Timeout(60) & Retry(3)', $configOk ? 'PASS' : 'FAIL', "timeout={$timeoutVal}, retry={$retryVal}"];
if ($configOk) $passed++; else $failed++;

// Test 7: Tool definitions count
$tools = $service->getToolDefinitions();
$toolCount = count($tools[0]['functionDeclarations'] ?? []);
$toolOk = $toolCount === 9;
$results[] = ['Test 7 - Tool definitions (9 tools)', $toolOk ? 'PASS' : 'FAIL', "{$toolCount} tools defined"];
if ($toolOk) $passed++; else $failed++;

// Test 8: Route exists
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$routeFound = false;
foreach ($routes as $route) {
    if ($route->getName() === 'admin.ai-chat.index') {
        $routeFound = true;
        break;
    }
}
$results[] = ['Test 8 - Route admin.ai-chat.index exists', $routeFound ? 'PASS' : 'FAIL', $routeFound ? 'Found' : 'Not found'];
if ($routeFound) $passed++; else $failed++;

// Test 9: View exists
$viewFound = view()->exists('admin.ai-chat');
$results[] = ['Test 9 - View admin.ai-chat exists', $viewFound ? 'PASS' : 'FAIL', $viewFound ? 'Found' : 'Not found'];
if ($viewFound) $passed++; else $failed++;

// Test 10: Livewire component exists
$lwFound = class_exists(\App\Livewire\Admin\AiChat::class);
$results[] = ['Test 10 - Livewire component exists', $lwFound ? 'PASS' : 'FAIL', $lwFound ? 'Found' : 'Not found'];
if ($lwFound) $passed++; else $failed++;

// Test 11: Migration check
$migrationFound = false;
$migrations = \Illuminate\Support\Facades\File::files(database_path('migrations'));
foreach ($migrations as $file) {
    if (str_contains($file->getFilename(), 'chat_logs')) {
        $migrationFound = true;
        break;
    }
}
$results[] = ['Test 11 - Migration chat_logs exists', $migrationFound ? 'PASS' : 'FAIL', $migrationFound ? 'Found' : 'Not found'];
if ($migrationFound) $passed++; else $failed++;

// Test 12: API documentation exists
$docFound = file_exists(__DIR__ . '/../docs/api/ai-chat.md');
$results[] = ['Test 12 - API docs exist', $docFound ? 'PASS' : 'FAIL', $docFound ? 'Found' : 'Not found'];
if ($docFound) $passed++; else $failed++;

echo "\n--- SUMMARY ---\n";
foreach ($results as $r) {
    $status = $r[1] === 'PASS' ? '[PASS]' : '[FAIL]';
    echo "{$status} {$r[0]}: {$r[2]}\n";
}

echo "\n========================================\n";
echo "RESULT: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================\n";

exit($failed > 0 ? 1 : 0);
