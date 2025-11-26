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
     * Send message to chatbot and get response with RAG integration
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:500',
                'session_id' => 'nullable|string',
                'timestamp' => 'nullable|integer',
                'use_rag' => 'nullable|boolean'
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
            $useRAG = $request->input('use_rag', true); // Default enable RAG
            
            Log::info('Chatbot message received', [
                'message' => $message,
                'session_id' => $sessionId,
                'use_rag' => $useRAG,
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

            // Smart RAG Integration - Balance context and general knowledge
            $contextData = ['has_context' => false, 'search_method' => 'general_knowledge'];
            
            if ($useRAG) {
                $contextData = $this->getRelevantContext($message);
                
                Log::info('Smart RAG Analysis', [
                    'has_context' => $contextData['has_context'],
                    'website' => $contextData['website_name'] ?? 'none',
                    'relevance_score' => $contextData['relevance_score'] ?? 0,
                    'search_method' => $contextData['search_method'] ?? 'none',
                    'is_general_question' => $contextData['is_general_question'] ?? false
                ]);
                
                // Even with no specific context, we can still use enhanced general knowledge
                if (!$contextData['has_context']) {
                    Log::info('No specific context found, using enhanced general knowledge');
                }
            } else {
                // RAG disabled - pure general knowledge mode
                Log::info('RAG disabled, using pure general knowledge mode');
            }
            
            // Generate AI response with Gemini API
            $response = $this->generateAIResponse($message, $contextData);
            
            // Batasi panjang response untuk user experience yang baik
            $response = $this->limitResponseLength($response);
            
            // Simpan percakapan untuk tracking
            $this->saveConversation($sessionId, $message, $response, $contextData['website_id'] ?? null);

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
     * Check if question is general or needs specific context
     */
    private function isGeneralQuestion(string $message): bool
    {
        $generalQuestionPatterns = [
            // Basic greetings and conversation
            '/\b(halo|hai|hello|hi|selamat|terima kasih|makasih)\b/i',
            
            // General knowledge questions with common topics
            '/\b(apa itu|apakah|bagaimana|mengapa|kenapa|siapa|dimana|kapan)\s.*(mobil|motor|kendaraan|sepeda|pesawat|kapal)\b/i',
            '/\b(apa itu|apakah|bagaimana|mengapa|kenapa)\s.*(bunga|tanaman|hewan|binatang|makanan|minuman)\b/i',
            '/\b(apa itu|apakah|bagaimana|mengapa|kenapa)\s.*(olahraga|sepakbola|basket|tennis|badminton|renang|lari|f1|formula|racing)\b/i',
            '/\b(jelaskan|definisi|pengertian|arti)\s.*(umum|dasar|basic|secara|general)\b/i',
            
            // Science, technology, general topics
            '/\b(teknologi|sains|sejarah|budaya|matematika|fisika|kimia|biologi|geografi|astronomi)\b/i',
            '/\b(programming|computer|internet|artificial intelligence|AI|machine learning|software|hardware)\b/i',
            
            // General natural topics
            '/\b(bunga|pohon|tanaman|hewan|binatang|ikan|burung|kucing|anjing|singa|gajah)\b/i',
            
            // General advice and how-to
            '/\b(tips|saran|cara|bagaimana)\s.*(umum|general|secara|pada umumnya|belajar|memasak|hidup)\b/i',
            
            // Identity questions
            '/\b(siapa kamu|nama kamu|apa kamu|kamu siapa|who are you)\b/i',
            
            // Entertainment and sports
            '/\b(film|musik|game|olahraga|sepakbola|basket|formula|f1|racing|motogp)\b/i',
            
            // Common objects and concepts not related to government/data
            '/\b(apa itu|pengertian|definisi)\s.*(?!jakarta|dki|pemerintah|data|statistik|metro|busway|transjakarta)\w+/i'
        ];
        
        // Check for government/database specific keywords that should use context
        $specificContextKeywords = [
            'jakarta', 'dki', 'pemerintah', 'pemda', 'gubernur', 'walikota',
            'metro', 'busway', 'transjakarta', 'mrt', 'transportasi jakarta',
            'data', 'statistik', 'dataset', 'satu data', 'portal data',
            'sakip', 'akuntabilitas', 'kinerja pemerintah', 'instansi',
            'pelayanan publik', 'e-government'
        ];
        
        $messageClean = strtolower($message);
        
        // If contains specific context keywords, don't treat as general
        foreach ($specificContextKeywords as $keyword) {
            if (strpos($messageClean, $keyword) !== false) {
                return false;
            }
        }
        
        // Check general patterns
        foreach ($generalQuestionPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get relevant context from database using improved hybrid search
     */
    private function getRelevantContext(string $message): array
    {
        $originalMessage = $message;
        $message = strtolower(trim($message));
        
        Log::info('Looking for relevant website for message: ' . $message);
        
        // Check if this is a general question that doesn't need specific context
        $isGeneral = $this->isGeneralQuestion($originalMessage);
        
        if ($isGeneral) {
            Log::info('Detected as general question, will use general knowledge');
            return [
                'context' => '',
                'website_id' => null,
                'website_name' => null,
                'has_context' => false,
                'relevance_score' => 0,
                'search_method' => 'general_knowledge',
                'is_general_question' => true
            ];
        }
        
        // 1. MySQL Full-text search (prioritas tertinggi)
        try {
            $searchResults = RagWebsite::smartSearch($message);
            
            if ($searchResults->isNotEmpty()) {
                $bestMatch = $searchResults->first();
                $relevanceScore = $bestMatch->relevance_score ?? 0.8; // Default high score for full-text match
                
                Log::info("Found best match via full-text search: {$bestMatch->name} (score: {$relevanceScore})");
                
                // Extract most relevant content section
                $relevantContent = $this->extractRelevantContent($message, $bestMatch->content);
                
                return [
                    'context' => $relevantContent,
                    'website_id' => $bestMatch->id,
                    'website_name' => $bestMatch->name,
                    'has_context' => true,
                    'relevance_score' => $relevanceScore,
                    'search_method' => 'fulltext'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Full-text search failed: ' . $e->getMessage());
        }
        
        // 2. Fallback: Manual relevance calculation
        $websites = RagWebsite::where('is_active', true)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->get();
            
        $bestMatch = null;
        $highestScore = 0;
        
        foreach ($websites as $website) {
            $score = $this->calculateRelevanceScore($message, $website);
            
            if ($score > $highestScore && $score > 0.1) { // Minimum relevance threshold
                $highestScore = $score;
                $bestMatch = $website;
            }
        }
        
        if ($bestMatch) {
            Log::info("Found best match via relevance calculation: {$bestMatch->name} (score: {$highestScore})");
            
            // Extract most relevant content section
            $relevantContent = $this->extractRelevantContent($message, $bestMatch->content);
            
            return [
                'context' => $relevantContent,
                'website_id' => $bestMatch->id,
                'website_name' => $bestMatch->name,
                'has_context' => true,
                'relevance_score' => $highestScore,
                'search_method' => 'relevance'
            ];
        }
        
        // 3. Fallback: Direct keyword matching in name/url/description
        $website = RagWebsite::where('is_active', true)
            ->where(function($query) use ($message) {
                $query->where('name', 'like', "%{$message}%")
                      ->orWhere('url', 'like', "%{$message}%")
                      ->orWhere('description', 'like', "%{$message}%");
            })
            ->first();
            
        if ($website) {
            Log::info('Found website by direct metadata match: ' . $website->name);
            return [
                'context' => substr($website->content ?? '', 0, 2000),
                'website_id' => $website->id,
                'website_name' => $website->name,
                'has_context' => !empty($website->content),
                'relevance_score' => 0.5,
                'search_method' => 'metadata'
            ];
        }
        
        // 4. Final fallback: Category-based keywords
        $contextKeywords = [
            'laravel' => ['laravel', 'framework', 'php', 'eloquent', 'blade', 'artisan', 'composer'],
            'programming' => ['programming', 'code', 'development', 'software', 'algorithm', 'function'],
            'web' => ['web', 'html', 'css', 'javascript', 'frontend', 'backend', 'api'],
            'database' => ['database', 'mysql', 'sql', 'query', 'table', 'migration'],
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
                            'context' => substr($website->content ?? '', 0, 2000),
                            'website_id' => $website->id,
                            'website_name' => $website->name,
                            'has_context' => !empty($website->content),
                            'relevance_score' => 0.3,
                            'search_method' => 'keyword'
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
            'has_context' => false,
            'relevance_score' => 0,
            'search_method' => 'none'
        ];
    }

    /**
     * Find website by category
     */
    private function findWebsiteByCategory(string $category): ?RagWebsite
    {
        $searchTerms = [
            'laravel' => 'laravel',
            'programming' => 'programming',
            'web' => 'web',
            'database' => 'database',
            'metro' => 'metro',
            'data' => 'data',
            'jakarta' => 'jakarta',
            'pbj' => 'pbj'
        ];

        $term = $searchTerms[$category] ?? $category;
        
        return RagWebsite::where('is_active', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                      ->orWhere('url', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%");
            })
            ->first();
    }

    /**
     * Calculate relevance score between message and website content
     */
    private function calculateRelevanceScore(string $message, RagWebsite $website): float
    {
        $score = 0;
        $messageWords = explode(' ', strtolower($message));
        $messageWords = array_filter($messageWords, function($word) {
            return strlen($word) > 2; // Skip short words
        });
        
        if (empty($messageWords)) {
            return 0;
        }
        
        $content = strtolower($website->content ?? '');
        $name = strtolower($website->name ?? '');
        $description = strtolower($website->description ?? '');
        $url = strtolower($website->url ?? '');
        
        $totalWords = count($messageWords);
        $matchedWords = 0;
        
        foreach ($messageWords as $word) {
            $wordScore = 0;
            
            // Check in name (highest weight)
            if (strpos($name, $word) !== false) {
                $wordScore += 3;
            }
            
            // Check in description (medium weight)
            if (strpos($description, $word) !== false) {
                $wordScore += 2;
            }
            
            // Check in URL (medium weight)
            if (strpos($url, $word) !== false) {
                $wordScore += 2;
            }
            
            // Check in content (lower weight but counted)
            if (strpos($content, $word) !== false) {
                $wordScore += 1;
                
                // Bonus for multiple occurrences in content
                $occurrences = substr_count($content, $word);
                if ($occurrences > 1) {
                    $wordScore += min($occurrences * 0.1, 1); // Max bonus of 1
                }
            }
            
            if ($wordScore > 0) {
                $matchedWords++;
                $score += $wordScore;
            }
        }
        
        // Normalize score
        $maxPossibleScore = $totalWords * 3; // If all words matched in name
        $normalizedScore = $score / $maxPossibleScore;
        
        // Bonus for high match percentage
        $matchPercentage = $matchedWords / $totalWords;
        if ($matchPercentage > 0.5) {
            $normalizedScore *= 1.2; // 20% bonus
        }
        
        return min($normalizedScore, 1.0); // Cap at 1.0
    }

    /**
     * Extract most relevant content section
     */
    private function extractRelevantContent(string $message, string $content): string
    {
        if (empty($content)) {
            return '';
        }
        
        $messageWords = explode(' ', strtolower($message));
        $messageWords = array_filter($messageWords, function($word) {
            return strlen($word) > 2;
        });
        
        if (empty($messageWords)) {
            return substr($content, 0, 2000);
        }
        
        // Split content into sentences
        $sentences = preg_split('/[.!?]+/', $content);
        $scoredSentences = [];
        
        foreach ($sentences as $index => $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) < 20) continue; // Skip very short sentences
            
            $sentenceLower = strtolower($sentence);
            $score = 0;
            
            foreach ($messageWords as $word) {
                $occurrences = substr_count($sentenceLower, $word);
                $score += $occurrences;
            }
            
            if ($score > 0) {
                $scoredSentences[] = [
                    'sentence' => $sentence,
                    'score' => $score,
                    'index' => $index
                ];
            }
        }
        
        // Sort by score descending
        usort($scoredSentences, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Take top 3-5 sentences
        $selectedSentences = array_slice($scoredSentences, 0, 5);
        
        // Sort back by original order
        usort($selectedSentences, function($a, $b) {
            return $a['index'] <=> $b['index'];
        });
        
        $extractedContent = '';
        foreach ($selectedSentences as $item) {
            $extractedContent .= $item['sentence'] . '. ';
        }
        
        // If extracted content is too short, add more context
        if (strlen($extractedContent) < 500 && strlen($content) > 500) {
            $extractedContent .= "\n\n" . substr($content, 0, 1500 - strlen($extractedContent));
        }
        
        return trim($extractedContent);
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
                "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key={$apiKey}",
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
        $hasContext = $contextData['has_context'] ?? false;
        $relevanceScore = $contextData['relevance_score'] ?? 0;
        $websiteName = $contextData['website_name'] ?? 'database';
        
        // Determine if context is highly relevant
        $isHighlyRelevant = $hasContext && $relevanceScore > 0.5;
        $isModeratelyRelevant = $hasContext && $relevanceScore > 0.1;
        
        if ($isHighlyRelevant) {
            // High relevance: Prioritize context but allow general knowledge
            $prompt = "Anda adalah AI Assistant yang membantu menjawab pertanyaan. Anda dapat menggunakan informasi dari database dan pengetahuan umum.

INSTRUKSI:
- Jawab SINGKAT dan JELAS dalam 2-4 kalimat
- PRIORITASKAN informasi dari CONTEXT karena sangat relevan (skor: " . round($relevanceScore, 2) . ")
- Jika context tidak lengkap, KOMBINASIKAN dengan pengetahuan umum
- Berikan jawaban yang komprehensif dan akurat
- Gunakan bahasa Indonesia yang mudah dipahami
- Sebutkan sumber jika menggunakan informasi spesifik dari database

CONTEXT DARI {$websiteName}:
{$contextData['context']}

PERTANYAAN: {$message}

JAWABAN (kombinasikan context dengan pengetahuan umum):";
        
        } elseif ($isModeratelyRelevant) {
            // Moderate relevance: Balance context and general knowledge
            $prompt = "Anda adalah AI Assistant yang membantu menjawab pertanyaan. Anda dapat menggunakan informasi dari database dan pengetahuan umum.

INSTRUKSI:
- Jawab SINGKAT dan JELAS dalam 2-4 kalimat
- Context tersedia dengan relevansi sedang (skor: " . round($relevanceScore, 2) . ")
- KOMBINASIKAN informasi context dengan pengetahuan umum
- Jika context kurang relevan, fokus pada pengetahuan umum
- Berikan jawaban yang berguna dan akurat
- Gunakan bahasa Indonesia yang mudah dipahami

CONTEXT DARI {$websiteName}:
{$contextData['context']}

PERTANYAAN: {$message}

JAWABAN (utamakan pengetahuan umum, pertimbangkan context):";
        
        } else {
            // No relevant context: Use general knowledge with enhanced capabilities
            $prompt = "Anda adalah AI Assistant yang membantu menjawab berbagai pertanyaan dengan pengetahuan umum yang luas.

KEMAMPUAN ANDA:
- Menjawab pertanyaan umum tentang teknologi, sains, sejarah, budaya
- Memberikan tips dan saran praktis
- Menjelaskan konsep dengan bahasa sederhana
- Membantu dengan informasi faktual dan edukatif

INSTRUKSI:
- Jawab SINGKAT dan JELAS dalam 2-4 kalimat
- Berikan informasi yang akurat dan berguna
- Gunakan bahasa Indonesia yang mudah dipahami
- Jika tidak yakin, sampaikan dengan jujur
- Berikan jawaban yang relevan dan membantu

PERTANYAAN: {$message}

JAWABAN BERDASARKAN PENGETAHUAN UMUM:";
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
                    
                    // Enhanced responses for general knowledge questions
                    
                    // Programming and development
                    if (preg_match('/\b(programming|coding|developer|software|algorithm|database|API|framework)\b/', $message)) {
                        return 'Programming adalah skill yang sangat berguna! Saya dapat membantu menjelaskan konsep dasar programming, bahasa pemrograman, dan pengembangan software. Ada yang ingin dipelajari?';
                    }
                    
                    // Science and mathematics
                    if (preg_match('/\b(matematika|fisika|kimia|biologi|sains|ilmu|rumus|perhitungan)\b/', $message)) {
                        return 'Sains dan matematika adalah dasar pengetahuan yang penting. Saya dapat membantu menjelaskan konsep dasar dan memberikan contoh sederhana. Topik apa yang ingin dibahas?';
                    }
                    
                    // History and culture
                    if (preg_match('/\b(sejarah|budaya|tradisi|adat|indonesia|dunia|peradaban)\b/', $message)) {
                        return 'Sejarah dan budaya sangat menarik untuk dipelajari! Saya dapat berbagi informasi tentang sejarah Indonesia, budaya nusantara, dan peradaban dunia. Ada periode atau topik tertentu?';
                    }
                    
                    // Business and economics
                    if (preg_match('/\b(bisnis|ekonomi|keuangan|investasi|usaha|entrepreneur|marketing)\b/', $message)) {
                        return 'Dunia bisnis dan ekonomi selalu berkembang. Saya dapat membantu menjelaskan konsep dasar bisnis, tips memulai usaha, dan prinsip-prinsip ekonomi. Ada yang ingin dibahas?';
                    }
                    
                    // Art and creativity
                    if (preg_match('/\b(seni|musik|lukis|gambar|desain|kreativitas|film|fotografi)\b/', $message)) {
                        return 'Seni dan kreativitas adalah ekspresi yang indah! Saya dapat berbagi tentang berbagai bentuk seni, teknik dasar, dan inspirasi kreatif. Bidang seni mana yang menarik bagi Anda?';
                    }
                    
                    // Travel and geography
                    if (preg_match('/\b(travel|wisata|tempat|negara|kota|geografi|liburan|destinasi)\b/', $message)) {
                        return 'Traveling membuka wawasan! Saya dapat berbagi informasi tentang destinasi wisata, budaya berbagai negara, dan tips perjalanan umum. Kemana Anda ingin tahu?';
                    }
                    
                    // General how-to questions
                    if (preg_match('/\b(cara|bagaimana|tutorial|langkah|tips|panduan)\b/', $message)) {
                        return 'Saya senang membantu memberikan panduan! Bisa dijelaskan lebih detail apa yang ingin Anda pelajari atau lakukan?';
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
     * Get RAG configuration endpoints for easy management
     */
    public function getRagConfig(): JsonResponse
    {
        try {
            $websites = RagWebsite::select('id', 'name', 'url', 'description', 'is_active', 'last_scraped_at')
                ->orderBy('is_active', 'desc')
                ->orderBy('name')
                ->get();

            $stats = [
                'total_websites' => $websites->count(),
                'active_websites' => $websites->where('is_active', true)->count(),
                'scraped_websites' => $websites->whereNotNull('last_scraped_at')->count()
            ];

            return response()->json([
                'success' => true,
                'websites' => $websites,
                'stats' => $stats,
                'message' => 'RAG configuration retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('RAG Config Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get RAG configuration'
            ], 500);
        }
    }

    /**
     * Update RAG website configuration
     */
    public function updateRagConfig(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'website_id' => 'required|exists:rag_websites,id',
                'is_active' => 'boolean',
                'description' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $website = RagWebsite::findOrFail($request->website_id);
            
            if ($request->has('is_active')) {
                $website->is_active = $request->is_active;
            }
            
            if ($request->has('description')) {
                $website->description = $request->description;
            }
            
            $website->save();

            Log::info('RAG Website Updated', [
                'website_id' => $website->id,
                'name' => $website->name,
                'is_active' => $website->is_active
            ]);

            return response()->json([
                'success' => true,
                'website' => $website->fresh(),
                'message' => 'RAG website updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('RAG Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update RAG configuration'
            ], 500);
        }
    }

    /**
     * Test RAG search functionality
     */
    public function testRagSearch(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|max:200'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = $request->input('query');
            $contextData = $this->getRelevantContext($query);

            return response()->json([
                'success' => true,
                'query' => $query,
                'context_data' => $contextData,
                'message' => 'RAG search test completed'
            ]);

        } catch (\Exception $e) {
            Log::error('RAG Search Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'RAG search test failed'
            ], 500);
        }
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
