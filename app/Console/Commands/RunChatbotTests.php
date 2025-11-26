<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunChatbotTests extends Command
{
    protected $signature = 'chatbot:run-tests {--output=console : Output format (console|json|html)}';
    protected $description = 'Run comprehensive chatbot tests with FAQ and general questions';

    private $results = [];
    private $startTime;
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;

    public function handle()
    {
        $this->startTime = microtime(true);
        $outputFormat = $this->option('output');

        $this->info('🧪 Starting Chatbot Test Suite...');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Load test questions
        $testFile = base_path('tests/chatbot_test_questions.json');
        
        if (!file_exists($testFile)) {
            $this->error('❌ Test file not found: ' . $testFile);
            return 1;
        }

        $testData = json_decode(file_get_contents($testFile), true);
        
        if (!$testData) {
            $this->error('❌ Invalid JSON in test file');
            return 1;
        }

        // Display test suite info
        $this->displayTestSuiteInfo($testData['test_suite']);
        $this->newLine();

        // Run FAQ tests
        $this->info('📚 Testing FAQ Questions (10 questions)');
        $this->info('─────────────────────────────────────────');
        $faqResults = $this->runTests($testData['faq_questions'], 'FAQ');
        $this->newLine();

        // Run general questions tests
        $this->info('🌐 Testing General Questions (20 questions)');
        $this->info('─────────────────────────────────────────');
        $generalResults = $this->runTests($testData['general_questions'], 'GENERAL');
        $this->newLine();

        // Calculate statistics
        $this->calculateStatistics($faqResults, $generalResults);

        // Display results based on format
        switch ($outputFormat) {
            case 'json':
                $this->outputJson();
                break;
            case 'html':
                $this->outputHtml();
                break;
            default:
                $this->outputConsole();
        }

        return 0;
    }

    private function displayTestSuiteInfo($suiteInfo)
    {
        $this->info('Test Suite: ' . $suiteInfo['name']);
        $this->info('Description: ' . $suiteInfo['description']);
        $this->info('Total Questions: ' . $suiteInfo['total_questions']);
        $this->info('Created: ' . $suiteInfo['created_at']);
    }

    private function runTests($questions, $type)
    {
        $results = [];
        $bar = $this->output->createProgressBar(count($questions));
        $bar->start();

        foreach ($questions as $index => $question) {
            $this->totalTests++;
            
            $result = $this->testQuestion($question, $type);
            $results[] = $result;

            if ($result['passed']) {
                $this->passedTests++;
            } else {
                $this->failedTests++;
            }

            $bar->advance();
            
            // Small delay to avoid overwhelming the server
            usleep(500000); // 0.5 second
        }

        $bar->finish();
        $this->newLine();

        return $results;
    }

    private function testQuestion($questionData, $type)
    {
        $startTime = microtime(true);
        
        try {
            // Call chatbot API
            $response = Http::timeout(30)
                ->post(url('/api/chatbot/message'), [
                    'message' => $questionData['question'],
                    'session_id' => 'test_session_' . time(),
                    'timestamp' => time()
                ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['message'] ?? $data['response'] ?? $data['bot_response'] ?? 'No response';
                
                // Validate response
                $validation = $this->validateResponse($answer, $questionData);
                
                $result = [
                    'id' => $questionData['id'],
                    'type' => $type,
                    'question' => $questionData['question'],
                    'category' => $questionData['category'],
                    'answer' => $answer,
                    'response_time_ms' => $responseTime,
                    'status_code' => $response->status(),
                    'passed' => $validation['passed'],
                    'validation' => $validation,
                    'expected_context' => $questionData['expected_context'],
                    'keywords' => $questionData['keywords']
                ];
                
                Log::info('Test ' . $questionData['id'] . ' completed', [
                    'question' => $questionData['question'],
                    'response_time' => $responseTime . 'ms',
                    'passed' => $validation['passed']
                ]);
                
                return $result;
                
            } else {
                $this->failedTests++;
                return [
                    'id' => $questionData['id'],
                    'type' => $type,
                    'question' => $questionData['question'],
                    'category' => $questionData['category'],
                    'answer' => 'API Error: ' . $response->status(),
                    'response_time_ms' => $responseTime,
                    'status_code' => $response->status(),
                    'passed' => false,
                    'validation' => ['passed' => false, 'reason' => 'API Error'],
                    'expected_context' => $questionData['expected_context'],
                    'keywords' => $questionData['keywords']
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'id' => $questionData['id'],
                'type' => $type,
                'question' => $questionData['question'],
                'category' => $questionData['category'],
                'answer' => 'Exception: ' . $e->getMessage(),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'status_code' => 0,
                'passed' => false,
                'validation' => ['passed' => false, 'reason' => 'Exception: ' . $e->getMessage()],
                'expected_context' => $questionData['expected_context'],
                'keywords' => $questionData['keywords']
            ];
        }
    }

    private function validateResponse($answer, $questionData)
    {
        // Validation criteria
        $validation = [
            'passed' => false,
            'has_content' => strlen($answer) > 10,
            'length_appropriate' => strlen($answer) >= 50 && strlen($answer) <= 1000,
            'contains_keywords' => false,
            'not_error' => !preg_match('/(error|exception|failed|gagal|maaf)/i', $answer),
            'is_helpful' => !preg_match('/(tidak tahu|tidak mengerti|tidak bisa)/i', $answer),
            'reason' => []
        ];

        // Check if response contains any keywords
        $keywordFound = false;
        foreach ($questionData['keywords'] as $keyword) {
            if (stripos($answer, $keyword) !== false) {
                $keywordFound = true;
                break;
            }
        }
        $validation['contains_keywords'] = $keywordFound;

        // Determine if passed
        $passedCriteria = 0;
        if ($validation['has_content']) $passedCriteria++;
        if ($validation['length_appropriate']) $passedCriteria++;
        if ($validation['not_error']) $passedCriteria++;
        if ($validation['is_helpful']) $passedCriteria++;

        // Pass if meets at least 3 out of 4 criteria
        $validation['passed'] = $passedCriteria >= 3;

        // Add reasons for failure
        if (!$validation['has_content']) {
            $validation['reason'][] = 'Response too short';
        }
        if (!$validation['length_appropriate']) {
            $validation['reason'][] = 'Response length not appropriate';
        }
        if (!$validation['not_error']) {
            $validation['reason'][] = 'Response contains error keywords';
        }
        if (!$validation['is_helpful']) {
            $validation['reason'][] = 'Response not helpful';
        }
        if (!$validation['contains_keywords']) {
            $validation['reason'][] = 'No relevant keywords found';
        }

        return $validation;
    }

    private function calculateStatistics($faqResults, $generalResults)
    {
        $this->results = [
            'faq' => $faqResults,
            'general' => $generalResults
        ];

        // Calculate response time statistics
        $allResults = array_merge($faqResults, $generalResults);
        $responseTimes = array_column($allResults, 'response_time_ms');
        
        $this->results['statistics'] = [
            'total_tests' => $this->totalTests,
            'passed' => $this->passedTests,
            'failed' => $this->failedTests,
            'success_rate' => round(($this->passedTests / $this->totalTests) * 100, 2),
            'avg_response_time' => round(array_sum($responseTimes) / count($responseTimes), 2),
            'min_response_time' => min($responseTimes),
            'max_response_time' => max($responseTimes),
            'total_execution_time' => round((microtime(true) - $this->startTime), 2)
        ];
    }

    private function outputConsole()
    {
        $stats = $this->results['statistics'];

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 TEST RESULTS SUMMARY');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Overall statistics
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Tests', $stats['total_tests']],
                ['✅ Passed', '<fg=green>' . $stats['passed'] . '</>'],
                ['❌ Failed', '<fg=red>' . $stats['failed'] . '</>'],
                ['Success Rate', $stats['success_rate'] . '%'],
                ['Avg Response Time', $stats['avg_response_time'] . ' ms'],
                ['Min Response Time', $stats['min_response_time'] . ' ms'],
                ['Max Response Time', $stats['max_response_time'] . ' ms'],
                ['Total Execution Time', $stats['total_execution_time'] . ' seconds']
            ]
        );

        $this->newLine();

        // FAQ Results
        $this->info('📚 FAQ Tests Results:');
        $faqPassed = count(array_filter($this->results['faq'], fn($r) => $r['passed']));
        $this->info("Passed: $faqPassed/10 (" . round(($faqPassed/10)*100, 1) . "%)");
        $this->newLine();

        // General Questions Results
        $this->info('🌐 General Questions Results:');
        $genPassed = count(array_filter($this->results['general'], fn($r) => $r['passed']));
        $this->info("Passed: $genPassed/20 (" . round(($genPassed/20)*100, 1) . "%)");
        $this->newLine();

        // Show failed tests
        $failedTests = array_filter(
            array_merge($this->results['faq'], $this->results['general']),
            fn($r) => !$r['passed']
        );

        if (count($failedTests) > 0) {
            $this->warn('⚠️  Failed Tests Details:');
            foreach ($failedTests as $test) {
                $this->error('─────────────────────────────────────────');
                $this->error('ID: ' . $test['id']);
                $this->error('Question: ' . $test['question']);
                $reason = is_array($test['validation']['reason']) ? implode(', ', $test['validation']['reason']) : $test['validation']['reason'];
                $this->error('Reason: ' . $reason);
                $this->line('Answer: ' . substr($test['answer'], 0, 150) . '...');
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        
        // Final verdict
        if ($stats['success_rate'] >= 90) {
            $this->info('🎉 EXCELLENT! Success rate: ' . $stats['success_rate'] . '%');
        } elseif ($stats['success_rate'] >= 75) {
            $this->info('✅ GOOD! Success rate: ' . $stats['success_rate'] . '%');
        } elseif ($stats['success_rate'] >= 60) {
            $this->warn('⚠️  NEEDS IMPROVEMENT! Success rate: ' . $stats['success_rate'] . '%');
        } else {
            $this->error('❌ POOR! Success rate: ' . $stats['success_rate'] . '%');
        }
    }

    private function outputJson()
    {
        $jsonOutput = json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $outputFile = storage_path('logs/chatbot_test_results_' . date('Y-m-d_His') . '.json');
        file_put_contents($outputFile, $jsonOutput);
        $this->info('✅ Results saved to: ' . $outputFile);
        $this->line($jsonOutput);
    }

    private function outputHtml()
    {
        $html = $this->generateHtmlReport();
        $outputFile = storage_path('logs/chatbot_test_results_' . date('Y-m-d_His') . '.html');
        file_put_contents($outputFile, $html);
        $this->info('✅ HTML report saved to: ' . $outputFile);
    }

    private function generateHtmlReport()
    {
        $stats = $this->results['statistics'];
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Test Results - ' . date('Y-m-d H:i:s') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 2em; }
        .stat-card p { margin: 10px 0 0 0; opacity: 0.9; }
        .test-results { margin-top: 30px; }
        .test-item { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ccc; }
        .test-item.passed { border-left-color: #4CAF50; }
        .test-item.failed { border-left-color: #f44336; }
        .test-item h4 { margin: 0 0 10px 0; color: #333; }
        .test-item .meta { color: #666; font-size: 0.9em; margin: 5px 0; }
        .test-item .answer { background: white; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .badge.success { background: #4CAF50; color: white; }
        .badge.error { background: #f44336; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Chatbot Test Results</h1>
        <p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>
        
        <div class="stats">
            <div class="stat-card">
                <h3>' . $stats['total_tests'] . '</h3>
                <p>Total Tests</p>
            </div>
            <div class="stat-card">
                <h3>' . $stats['passed'] . '</h3>
                <p>Passed</p>
            </div>
            <div class="stat-card">
                <h3>' . $stats['failed'] . '</h3>
                <p>Failed</p>
            </div>
            <div class="stat-card">
                <h3>' . $stats['success_rate'] . '%</h3>
                <p>Success Rate</p>
            </div>
            <div class="stat-card">
                <h3>' . $stats['avg_response_time'] . 'ms</h3>
                <p>Avg Response Time</p>
            </div>
            <div class="stat-card">
                <h3>' . $stats['total_execution_time'] . 's</h3>
                <p>Total Time</p>
            </div>
        </div>
        
        <div class="test-results">
            <h2>📚 FAQ Tests Results</h2>';
        
        foreach ($this->results['faq'] as $test) {
            $statusClass = $test['passed'] ? 'passed' : 'failed';
            $statusBadge = $test['passed'] ? '<span class="badge success">✓ PASSED</span>' : '<span class="badge error">✗ FAILED</span>';
            
            $html .= '
            <div class="test-item ' . $statusClass . '">
                <h4>' . htmlspecialchars($test['id']) . ' - ' . htmlspecialchars($test['question']) . ' ' . $statusBadge . '</h4>
                <div class="meta">
                    <strong>Category:</strong> ' . htmlspecialchars($test['category']) . ' | 
                    <strong>Response Time:</strong> ' . $test['response_time_ms'] . 'ms | 
                    <strong>Expected Context:</strong> ' . htmlspecialchars($test['expected_context']) . '
                </div>
                <div class="answer"><strong>Answer:</strong> ' . htmlspecialchars($test['answer']) . '</div>
            </div>';
        }
        
        $html .= '
            <h2>🌐 General Questions Results</h2>';
        
        foreach ($this->results['general'] as $test) {
            $statusClass = $test['passed'] ? 'passed' : 'failed';
            $statusBadge = $test['passed'] ? '<span class="badge success">✓ PASSED</span>' : '<span class="badge error">✗ FAILED</span>';
            
            $html .= '
            <div class="test-item ' . $statusClass . '">
                <h4>' . htmlspecialchars($test['id']) . ' - ' . htmlspecialchars($test['question']) . ' ' . $statusBadge . '</h4>
                <div class="meta">
                    <strong>Category:</strong> ' . htmlspecialchars($test['category']) . ' | 
                    <strong>Response Time:</strong> ' . $test['response_time_ms'] . 'ms | 
                    <strong>Expected Context:</strong> ' . htmlspecialchars($test['expected_context']) . '
                </div>
                <div class="answer"><strong>Answer:</strong> ' . htmlspecialchars($test['answer']) . '</div>
            </div>';
        }
        
        $html .= '
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
