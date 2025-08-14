<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\RagWebsite;
use App\Models\Conversation;

class ChatbotController extends Controller
{
    private const GEMINI_TIMEOUT = 30;
    private const MAX_RESPONSE_LENGTH = 400; // Batasi untuk 2-4 kalimat
    
    /**
     * Send message to chatbot and get response
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:500',
                'session_id' => 'nullable|string',
                'timestamp' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = trim($request->input('message'));
            $sessionId = $request->input('session_id', 'default_session');
            
            Log::info('Chatbot message received', [
                'message' => $message,
                'session_id' => $sessionId,
                'ip' => $request->ip()
            ]);

            // Filter kata kasar
            if ($this->containsProfanity($message)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mohon gunakan bahasa yang sopan. Saya siap membantu dengan pertanyaan lain.',
                    'bot_response' => 'Mohon gunakan bahasa yang sopan. Saya siap membantu dengan pertanyaan lain.',
                    'response' => 'Mohon gunakan bahasa yang sopan. Saya siap membantu dengan pertanyaan lain.',
                    'session_id' => $sessionId,
                    'timestamp' => now()->timestamp,
                    'model' => 'chatbot-v1.0'
                ], 200);
            }

            // Cek apakah ada context khusus dari database
            $contextData = $this->getRelevantContext($message);
            
            // Generate response
            $response = $this->generateAIResponse($message, $contextData);
            
            // Batasi panjang response
            $response = $this->limitResponseLength($response);
            
            // Simpan percakapan
            $this->saveConversation($sessionId, $message, $response, $contextData['website_id']);

            return response()->json([
                'success' => true,
                'message' => $response,
                'bot_response' => $response,
                'response' => $response,
                'session_id' => $sessionId,
                'timestamp' => now()->timestamp,
                'model' => 'chatbot-v1.0'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chatbot error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get chatbot status
     */
    public function getStatus(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'version' => '1.0.0',
            'timestamp' => now()->timestamp,
            'uptime' => '99.9%'
        ]);
    }

    /**
     * Filter profanity
     */
    private function containsProfanity(string $message): bool
    {
        $profanityWords = [
            'anjing', 'babi', 'bangsat', 'bajingan', 'brengsek', 'kontol', 'memek', 
            'ngentot', 'jancuk', 'keparat', 'sialan', 'tolol', 'bodoh', 'goblok',
            'fuck', 'shit', 'damn', 'bitch', 'asshole'
        ];
        
        $message = strtolower($message);
        foreach ($profanityWords as $word) {
            if (strpos($message, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get relevant context from database
     */
    private function getRelevantContext(string $message): array
    {
        $message = strtolower($message);
        
        Log::info('Looking for relevant website for message: ' . $message);
        
        // Cari website berdasarkan keyword langsung di nama
        $website = RagWebsite::where('is_active', true)
            ->where(function($query) use ($message) {
                $query->where('name', 'like', "%{$message}%")
                      ->orWhere('url', 'like', "%{$message}%")
                      ->orWhere('description', 'like', "%{$message}%");
            })
            ->first();
            
        if ($website) {
            Log::info('Found website by direct match: ' . $website->name);
            return [
                'context' => substr($website->content, 0, 2000),
                'website_id' => $website->id,
                'website_name' => $website->name,
                'has_context' => true
            ];
        }
        
        // Keywords untuk identifikasi context
        $contextKeywords = [
            'metro' => ['metro', 'peta', 'maps', 'navigasi', 'rute', 'transportasi'],
            'data' => ['data', 'dataset', 'statistik', 'satu data', 'informasi'],
            'jakarta' => ['jakarta', 'dki', 'ibukota', 'gubernur'],
            'pbj' => ['pbj', 'pengadaan', 'tender', 'lelang']
        ];

        foreach ($contextKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $website = $this->findWebsiteByCategory($category);
                    if ($website) {
                        Log::info('Found ' . $website->name . ' website by keyword match: ' . $keyword);
                        return [
                            'context' => substr($website->content, 0, 2000),
                            'website_id' => $website->id,
                            'website_name' => $website->name,
                            'has_context' => true
                        ];
                    }
                }
            }
        }
        
        Log::info('No relevant website found for message: ' . $message);

        return [
            'context' => '',
            'website_id' => null,
            'website_name' => null,
            'has_context' => false
        ];
    }

    /**
     * Find website by category
     */
    private function findWebsiteByCategory(string $category): ?RagWebsite
    {
        $searchTerms = [
            'metro' => 'metro',
            'data' => 'data',
            'jakarta' => 'jakarta',
            'pbj' => 'pbj'
        ];

        $term = $searchTerms[$category] ?? $category;
        
        return RagWebsite::where('is_active', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                      ->orWhere('url', 'like', "%{$term}%");
            })
            ->first();
    }

    /**
     * Generate AI response
     */
    private function generateAIResponse(string $message, array $contextData): string
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            
            Log::info('Gemini API call', [
                'key_present' => !empty($apiKey),
                'key_length' => $apiKey ? strlen($apiKey) : 0,
                'prompt_length' => strlen($this->buildPrompt($message, $contextData))
            ]);
            
            if (!$apiKey) {
                Log::error('GEMINI API KEY NOT FOUND!');
                return $this->getFallbackResponse($message);
            }

            $prompt = $this->buildPrompt($message, $contextData);
            
            if ($contextData['has_context']) {
                Log::info('Using context from website: ' . $contextData['website_name']);
            }
            
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 200,
                    'topP' => 0.8,
                    'topK' => 40
                ]
            ];
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->withOptions([
                'verify' => false,
                'timeout' => self::GEMINI_TIMEOUT
            ])->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                $requestData
            );

            Log::info('Gemini API response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $aiResponse = trim($data['candidates'][0]['content']['parts'][0]['text']);
                    Log::info('Gemini API success', ['response_length' => strlen($aiResponse)]);
                    return $aiResponse;
                } 
                
                if (isset($data['candidates'][0]['finishReason'])) {
                    Log::warning('Gemini finished with reason', ['reason' => $data['candidates'][0]['finishReason']]);
                    return "Maaf, tidak dapat memberikan respons untuk pertanyaan tersebut. Silakan coba pertanyaan lain.";
                }
                
                Log::error('Unexpected Gemini response structure', ['data' => $data]);
            } else {
                $errorData = $response->json();
                
                // Check for quota exceeded
                if ($response->status() === 429 || 
                    (isset($errorData['error']['status']) && $errorData['error']['status'] === 'RESOURCE_EXHAUSTED')) {
                    Log::error('Gemini API failed', [
                        'status' => $response->status(),
                        'error_data' => $errorData
                    ]);
                    Log::error('Gemini API failed: ' . $errorData['error']['message']);
                    Log::info('Handling fallback response for message: ' . $message);
                    return $this->getFallbackResponse($message);
                }
                
                Log::error('Gemini API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return $this->getFallbackResponse($message);

        } catch (\Exception $e) {
            Log::error('Gemini API Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return $this->getFallbackResponse($message);
        }
    }

    /**
     * Build AI prompt
     */
    private function buildPrompt(string $message, array $contextData): string
    {
        if ($contextData['has_context']) {
            $prompt = "Anda adalah AI Assistant yang membantu menjawab pertanyaan tentang website/layanan tertentu.

INSTRUKSI:
- Jawab SINGKAT dalam 2-4 kalimat saja
- Gunakan informasi dari context jika relevan
- Jika context tidak sesuai, jawab berdasarkan pengetahuan umum
- Gunakan bahasa Indonesia yang mudah dipahami

CONTEXT: {$contextData['context']}

PERTANYAAN: {$message}

JAWABAN SINGKAT:";
        } else {
            $prompt = "Anda adalah AI Assistant yang membantu menjawab pertanyaan umum.

INSTRUKSI:
- Jawab SINGKAT dalam 2-4 kalimat saja
- Berikan informasi yang akurat dan berguna
- Gunakan bahasa Indonesia yang mudah dipahami
- Jika tidak tahu pasti, katakan dengan jelas

PERTANYAAN: {$message}

JAWABAN SINGKAT:";
        }

        return $prompt;
    }

    /**
     * Fallback response untuk saat API gagal
     */
    private function getFallbackResponse(string $message): string
    {
        $message = strtolower($message);
        
        // Respon untuk salam
        if (preg_match('/\b(halo|hai|hello|hi|selamat)\b/', $message)) {
            return 'Halo! Saya AI Assistant yang siap membantu menjawab pertanyaan Anda. Ada yang ingin ditanyakan?';
        }
        
        // Respon untuk ucapan terima kasih
        if (preg_match('/\b(terima kasih|makasih|thanks)\b/', $message)) {
            return 'Sama-sama! Senang bisa membantu. Jangan ragu untuk bertanya lagi.';
        }
        
        // Respon untuk pertanyaan tentang identitas
        if (preg_match('/\b(siapa kamu|nama kamu|apa kamu|kamu siapa)\b/', $message)) {
            return 'Saya AI Assistant, chatbot yang dapat membantu menjawab berbagai pertanyaan. Silakan tanyakan apa saja!';
        }
        
        // Respon untuk pertanyaan apa kabar
        if (preg_match('/\b(apa kabar|bagaimana kabar|how are you)\b/', $message)) {
            return 'Kabar baik! Saya siap membantu menjawab pertanyaan Anda. Ada yang ingin ditanyakan?';
        }
        
        // Respon untuk pertanyaan teknologi
        if (preg_match('/\b(teknologi|komputer|laptop|hp|smartphone|internet|website|aplikasi)\b/', $message)) {
            return 'Teknologi berkembang pesat! Saya dapat membantu menjelaskan tentang komputer, smartphone, internet, dan teknologi lainnya. Tanyakan yang lebih spesifik!';
        }
        
        // Respon untuk pertanyaan pendidikan
        if (preg_match('/\b(sekolah|universitas|kuliah|belajar|pendidikan|les)\b/', $message)) {
            return 'Pendidikan sangat penting untuk masa depan. Saya dapat membantu dengan informasi tentang sekolah, universitas, dan tips belajar. Ada yang ingin ditanyakan?';
        }
        
        // Respon untuk pertanyaan kesehatan
        if (preg_match('/\b(kesehatan|sakit|obat|dokter|rumah sakit|vitamin|diet)\b/', $message)) {
            return 'Kesehatan adalah hal terpenting. Untuk masalah kesehatan serius, sebaiknya konsultasi dengan dokter. Saya dapat memberikan informasi umum tentang hidup sehat.';
        }
        
        // Respon untuk pertanyaan makanan
        if (preg_match('/\b(makanan|makan|masak|resep|kuliner|restoran)\b/', $message)) {
            return 'Makanan adalah kebutuhan pokok yang penting. Saya dapat membantu dengan informasi dasar tentang nutrisi dan tips memasak sederhana. Tanyakan yang spesifik!';
        }
        
        // Respon untuk pertanyaan olahraga
        if (preg_match('/\b(olahraga|sepakbola|basket|badminton|lari|fitness|gym)\b/', $message)) {
            return 'Olahraga sangat baik untuk kesehatan! Saya dapat memberikan informasi dasar tentang berbagai jenis olahraga dan manfaatnya untuk tubuh.';
        }
        
        // Respon untuk pertanyaan cuaca/waktu
        if (preg_match('/\b(cuaca|hujan|panas|dingin|waktu|jam|tanggal)\b/', $message)) {
            return 'Untuk informasi cuaca dan waktu real-time, sebaiknya cek aplikasi cuaca atau jam di perangkat Anda. Saya tidak memiliki akses data real-time.';
        }
        
        // Respon untuk pertanyaan umum dengan kata tanya
        if (preg_match('/^(apa|siapa|dimana|kapan|bagaimana|mengapa|kenapa|berapa)\s/', $message)) {
            return 'Pertanyaan yang menarik! Saya siap membantu menjawab. Bisa dijelaskan lebih detail tentang apa yang ingin Anda ketahui?';
        }
        
        // Respon untuk Metro Maps
        if (preg_match('/\b(metro|peta|maps|navigasi|rute|transportasi)\b/', $message)) {
            return 'Metro Maps adalah layanan peta dan navigasi untuk transportasi kota. Untuk informasi detail tentang rute dan jadwal, silakan kunjungi website resmi atau aplikasi Metro Maps.';
        }
        
        // Respon untuk Data/Satu Data
        if (preg_match('/\b(data|dataset|statistik|satu data|informasi)\b/', $message)) {
            return 'Portal Satu Data menyediakan berbagai dataset dan statistik pemerintah. Anda dapat mengakses data demografi, ekonomi, dan sosial di portal resmi Satu Data Indonesia.';
        }
        
        // Respon untuk Jakarta
        if (preg_match('/\b(jakarta|dki|ibukota|gubernur|pemda)\b/', $message)) {
            return 'Jakarta adalah ibu kota Indonesia dengan berbagai layanan pemerintahan dan fasilitas publik. Untuk informasi resmi tentang Jakarta, kunjungi portal jakarta.go.id.';
        }
        
        // Respon default yang lebih informatif
        return 'Saya AI Assistant yang dapat membantu dengan berbagai topik seperti teknologi, pendidikan, kesehatan, dan informasi umum. Silakan tanyakan yang lebih spesifik agar saya dapat membantu lebih baik!';
    }

    /**
     * Limit response length
     */
    private function limitResponseLength(string $response): string
    {
        if (strlen($response) <= self::MAX_RESPONSE_LENGTH) {
            return $response;
        }
        
        // Cari titik terakhir dalam batas karakter
        $truncated = substr($response, 0, self::MAX_RESPONSE_LENGTH);
        $lastPeriod = strrpos($truncated, '.');
        
        if ($lastPeriod !== false && $lastPeriod > self::MAX_RESPONSE_LENGTH * 0.7) {
            return substr($response, 0, $lastPeriod + 1);
        }
        
        return $truncated . '...';
    }

    /**
     * Save conversation
     */
    private function saveConversation(string $sessionId, string $userMessage, string $botResponse, ?int $websiteId): void
    {
        try {
            Conversation::create([
                'session_id' => $sessionId,
                'user_message' => $userMessage,
                'bot_response' => $botResponse,
                'website_id' => $websiteId,
                'metadata' => [
                    'has_context' => !is_null($websiteId),
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save conversation: ' . $e->getMessage());
        }
    }
}
