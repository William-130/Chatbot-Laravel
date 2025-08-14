<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ChatbotController;
use Illuminate\Http\Request;

class TestChatbot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:test 
                           {message? : Message to send to chatbot}
                           {--interactive : Start interactive chat session}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test chatbot functionality with RAG integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('interactive')) {
            return $this->interactiveChat();
        }

        $message = $this->argument('message');
        
        if (!$message) {
            $message = $this->ask('What would you like to ask the chatbot?');
        }

        $this->testSingleMessage($message);
        return Command::SUCCESS;
    }

    private function testSingleMessage(string $message)
    {
        $this->info("🤖 Testing chatbot with message: '{$message}'");
        $this->newLine();

        // Create a mock request
        $request = Request::create('/api/chatbot/message', 'POST', [
            'message' => $message,
            'session_id' => 'test_session_' . time()
        ]);

        $controller = new ChatbotController();
        
        try {
            $response = $controller->sendMessage($request);
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                $this->line("🔍 **Response:**");
                $this->line($data['response']);
                $this->newLine();
                
                $this->line("📊 **Metadata:**");
                $this->line("- Session ID: {$data['session_id']}");
                $this->line("- Timestamp: " . date('Y-m-d H:i:s', $data['timestamp']));
                $this->line("- Model: {$data['model']}");
            } else {
                $this->error("❌ Chatbot failed: {$data['message']}");
                if (isset($data['error'])) {
                    $this->line("Error: {$data['error']}");
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: {$e->getMessage()}");
        }
    }

    private function interactiveChat()
    {
        $this->info("🚀 Starting interactive chatbot session...");
        $this->line("Type 'exit' or 'quit' to stop the session.");
        $this->newLine();

        $sessionId = 'interactive_' . time();
        $controller = new ChatbotController();
        
        while (true) {
            $message = $this->ask('You');
            
            if (in_array(strtolower(trim($message)), ['exit', 'quit', 'stop'])) {
                $this->info("👋 Goodbye! Chat session ended.");
                break;
            }

            if (empty(trim($message))) {
                $this->warn("Please enter a message.");
                continue;
            }

            $this->newLine();
            $this->line("🤖 **Bot:**");

            // Create request
            $request = Request::create('/api/chatbot/message', 'POST', [
                'message' => $message,
                'session_id' => $sessionId
            ]);

            try {
                $response = $controller->sendMessage($request);
                $data = json_decode($response->getContent(), true);

                if ($data['success']) {
                    $this->line($data['response']);
                } else {
                    $this->error("❌ Error: {$data['message']}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Exception: {$e->getMessage()}");
            }

            $this->newLine();
        }

        return Command::SUCCESS;
    }
}
