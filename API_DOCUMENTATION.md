# 🔌 API Documentation

Dokumentasi lengkap untuk Chatbot API endpoints dan integrasi.

## 📋 Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Request/Response Format](#request-response-format)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Testing](#testing)
- [SDK & Libraries](#sdk--libraries)

## 🔎 Overview

Chatbot API menyediakan endpoint untuk:
- ✅ Mengirim dan menerima pesan
- ✅ Mengintegrasikan dengan RAG (Retrieval-Augmented Generation)
- ✅ Manajemen context dan conversational memory
- ✅ Real-time response dengan WebSockets (opsional)

**Base URL:** `https://your-domain.com/api`

**API Version:** v1

**Content-Type:** `application/json`

## 🔐 Authentication

### API Key (Recommended)
```bash
curl -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     https://your-domain.com/api/chatbot/message
```

### Session-based (Web Widget)
```javascript
// Widget automatically handles session authentication
// No additional headers needed
```

### CSRF Protection
```javascript
// For web requests, include CSRF token
fetch('/api/chatbot/message', {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
    }
});
```

## 📡 Endpoints

### 1. Send Message

**Endpoint:** `POST /api/chatbot/message`

**Description:** Kirim pesan ke chatbot dan terima response.

#### Request
```json
{
    "message": "Hello, how can you help me?",
    "conversation_id": "uuid-optional",
    "context": {
        "user_name": "John Doe",
        "page_url": "https://example.com/contact",
        "custom_data": {}
    }
}
```

#### Response
```json
{
    "success": true,
    "data": {
        "response": "Hello! I'm here to help you. What do you need assistance with?",
        "conversation_id": "550e8400-e29b-41d4-a716-446655440000",
        "timestamp": "2024-01-15T10:30:00Z",
        "metadata": {
            "response_time": 1250,
            "tokens_used": 45,
            "sources": [
                {
                    "title": "FAQ - Customer Support",
                    "url": "https://example.com/faq",
                    "relevance": 0.85
                }
            ]
        }
    }
}
```

#### cURL Example
```bash
curl -X POST https://your-domain.com/api/chatbot/message \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{
    "message": "What are your business hours?",
    "conversation_id": "550e8400-e29b-41d4-a716-446655440000"
  }'
```

#### JavaScript Example
```javascript
async function sendMessage(message, conversationId = null) {
    try {
        const response = await fetch('/api/chatbot/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                message: message,
                conversation_id: conversationId,
                context: {
                    page_url: window.location.href,
                    user_agent: navigator.userAgent
                }
            })
        });

        const data = await response.json();
        
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.message || 'API request failed');
        }
    } catch (error) {
        console.error('Chatbot API Error:', error);
        throw error;
    }
}
```

### 2. Get Conversation History

**Endpoint:** `GET /api/chatbot/conversation/{conversation_id}`

**Description:** Ambil riwayat percakapan.

#### Request
```
GET /api/chatbot/conversation/550e8400-e29b-41d4-a716-446655440000
```

#### Response
```json
{
    "success": true,
    "data": {
        "conversation_id": "550e8400-e29b-41d4-a716-446655440000",
        "created_at": "2024-01-15T10:00:00Z",
        "updated_at": "2024-01-15T10:30:00Z",
        "messages": [
            {
                "id": 1,
                "type": "user",
                "message": "Hello!",
                "timestamp": "2024-01-15T10:00:00Z"
            },
            {
                "id": 2,
                "type": "bot",
                "message": "Hello! How can I help you?",
                "timestamp": "2024-01-15T10:00:05Z",
                "metadata": {
                    "response_time": 850,
                    "tokens_used": 12
                }
            }
        ],
        "total_messages": 2
    }
}
```

### 3. Delete Conversation

**Endpoint:** `DELETE /api/chatbot/conversation/{conversation_id}`

**Description:** Hapus percakapan dan semua riwayatnya.

#### Request
```
DELETE /api/chatbot/conversation/550e8400-e29b-41d4-a716-446655440000
```

#### Response
```json
{
    "success": true,
    "message": "Conversation deleted successfully"
}
```

### 4. Health Check

**Endpoint:** `GET /api/chatbot/health`

**Description:** Cek status API dan sistem.

#### Response
```json
{
    "success": true,
    "data": {
        "status": "healthy",
        "timestamp": "2024-01-15T10:30:00Z",
        "version": "2.1.0",
        "uptime": "5 days 3 hours",
        "services": {
            "database": "connected",
            "ai_model": "ready",
            "rag_search": "operational"
        }
    }
}
```

## 📋 Request/Response Format

### Standard Request Format
```json
{
    "message": "string (required)",
    "conversation_id": "string (optional, UUID)",
    "context": {
        "user_name": "string (optional)",
        "page_url": "string (optional)",
        "language": "string (optional, default: id-ID)",
        "custom_data": "object (optional)"
    },
    "options": {
        "stream": "boolean (optional, default: false)",
        "max_tokens": "integer (optional, default: 150)",
        "temperature": "float (optional, default: 0.7)"
    }
}
```

### Standard Response Format
```json
{
    "success": "boolean",
    "data": "object (if success)",
    "message": "string (error message if failed)",
    "errors": "array (validation errors if any)",
    "meta": {
        "timestamp": "string (ISO 8601)",
        "request_id": "string (UUID)",
        "version": "string"
    }
}
```

### Error Response Format
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "message": [
            "The message field is required."
        ]
    },
    "meta": {
        "timestamp": "2024-01-15T10:30:00Z",
        "request_id": "550e8400-e29b-41d4-a716-446655440000",
        "version": "2.1.0"
    }
}
```

## ⚠️ Error Handling

### HTTP Status Codes

| Code | Description | Action |
|------|-------------|--------|
| 200 | Success | Request processed successfully |
| 400 | Bad Request | Check request format and parameters |
| 401 | Unauthorized | Check API key or authentication |
| 403 | Forbidden | Insufficient permissions |
| 422 | Validation Error | Check request validation errors |
| 429 | Rate Limited | Wait before retrying |
| 500 | Server Error | Contact support |
| 503 | Service Unavailable | Retry later |

### Error Types

#### 1. Validation Errors (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "message": ["Message is required"],
        "conversation_id": ["Invalid UUID format"]
    }
}
```

#### 2. Rate Limit Error (429)
```json
{
    "success": false,
    "message": "Rate limit exceeded",
    "retry_after": 60,
    "limit": {
        "requests": 100,
        "period": "hour"
    }
}
```

#### 3. Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error",
    "error_id": "ERR_12345",
    "support_contact": "support@your-domain.com"
}
```

### Error Handling Best Practices

```javascript
async function handleChatbotResponse(message) {
    try {
        const response = await fetch('/api/chatbot/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ message })
        });

        const data = await response.json();

        // Handle different status codes
        switch (response.status) {
            case 200:
                return data.data;
                
            case 400:
                throw new Error(`Bad Request: ${data.message}`);
                
            case 401:
                // Redirect to login or refresh token
                window.location.href = '/login';
                break;
                
            case 422:
                // Show validation errors
                const errorMessages = Object.values(data.errors).flat();
                throw new Error(errorMessages.join(', '));
                
            case 429:
                // Rate limited - wait and retry
                const retryAfter = data.retry_after || 60;
                throw new Error(`Rate limited. Try again in ${retryAfter} seconds.`);
                
            case 500:
                throw new Error('Server error. Please try again later.');
                
            default:
                throw new Error(`Unexpected error: ${response.status}`);
        }
    } catch (error) {
        if (error.name === 'TypeError') {
            // Network error
            throw new Error('Network error. Please check your connection.');
        }
        throw error;
    }
}
```

## 🚦 Rate Limiting

### Default Limits

| Plan | Requests/Hour | Requests/Day | Concurrent |
|------|---------------|--------------|------------|
| Free | 100 | 1,000 | 5 |
| Basic | 1,000 | 10,000 | 10 |
| Pro | 5,000 | 50,000 | 25 |
| Enterprise | Unlimited | Unlimited | 100 |

### Rate Limit Headers
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 995
X-RateLimit-Reset: 1642176000
X-RateLimit-Retry-After: 3600
```

### Handling Rate Limits
```javascript
function handleRateLimit(response) {
    const remaining = response.headers.get('X-RateLimit-Remaining');
    const resetTime = response.headers.get('X-RateLimit-Reset');
    
    if (remaining < 10) {
        console.warn(`Rate limit warning: ${remaining} requests remaining`);
    }
    
    if (response.status === 429) {
        const retryAfter = response.headers.get('X-RateLimit-Retry-After');
        throw new RateLimitError(`Rate limited. Retry after ${retryAfter} seconds`);
    }
}
```

## 🧪 Testing

### Test Endpoint

**Endpoint:** `POST /api/chatbot/test`

**Description:** Test API connectivity and basic functionality.

```bash
curl -X POST https://your-domain.com/api/chatbot/test \
  -H "Content-Type: application/json" \
  -d '{"test": "ping"}'
```

**Response:**
```json
{
    "success": true,
    "data": {
        "message": "pong",
        "timestamp": "2024-01-15T10:30:00Z",
        "server_time": 1642176000
    }
}
```

### Testing Checklist

- [ ] Basic message sending
- [ ] Conversation persistence
- [ ] Error handling
- [ ] Rate limiting
- [ ] Authentication
- [ ] CORS configuration
- [ ] Response time
- [ ] Large message handling

### Test Scripts

#### Node.js Test Script
```javascript
const axios = require('axios');

const API_BASE = 'https://your-domain.com/api';
const API_KEY = 'your-api-key';

async function testChatbotAPI() {
    console.log('🧪 Testing Chatbot API...\n');

    try {
        // Test 1: Health Check
        console.log('1. Testing health check...');
        const health = await axios.get(`${API_BASE}/chatbot/health`);
        console.log('✅ Health check passed\n');

        // Test 2: Send Message
        console.log('2. Testing message sending...');
        const message = await axios.post(`${API_BASE}/chatbot/message`, {
            message: 'Hello, this is a test message'
        }, {
            headers: {
                'Authorization': `Bearer ${API_KEY}`,
                'Content-Type': 'application/json'
            }
        });
        
        const conversationId = message.data.data.conversation_id;
        console.log('✅ Message sent successfully');
        console.log(`   Conversation ID: ${conversationId}\n`);

        // Test 3: Get Conversation
        console.log('3. Testing conversation retrieval...');
        const conversation = await axios.get(`${API_BASE}/chatbot/conversation/${conversationId}`, {
            headers: {
                'Authorization': `Bearer ${API_KEY}`
            }
        });
        console.log('✅ Conversation retrieved successfully\n');

        console.log('🎉 All tests passed!');

    } catch (error) {
        console.error('❌ Test failed:', error.response?.data || error.message);
    }
}

testChatbotAPI();
```

#### PHP Test Script
```php
<?php

function testChatbotAPI() {
    $apiBase = 'https://your-domain.com/api';
    $apiKey = 'your-api-key';
    
    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ];
    
    echo "🧪 Testing Chatbot API...\n\n";
    
    try {
        // Test Health Check
        echo "1. Testing health check...\n";
        $response = makeRequest('GET', $apiBase . '/chatbot/health', null, $headers);
        echo "✅ Health check passed\n\n";
        
        // Test Message Sending
        echo "2. Testing message sending...\n";
        $messageData = json_encode(['message' => 'Hello from PHP test']);
        $response = makeRequest('POST', $apiBase . '/chatbot/message', $messageData, $headers);
        $data = json_decode($response, true);
        
        if ($data['success']) {
            $conversationId = $data['data']['conversation_id'];
            echo "✅ Message sent successfully\n";
            echo "   Conversation ID: $conversationId\n\n";
            
            // Test Conversation Retrieval
            echo "3. Testing conversation retrieval...\n";
            $response = makeRequest('GET', $apiBase . '/chatbot/conversation/' . $conversationId, null, $headers);
            echo "✅ Conversation retrieved successfully\n\n";
            
            echo "🎉 All tests passed!\n";
        } else {
            throw new Exception('Message sending failed: ' . $data['message']);
        }
        
    } catch (Exception $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
    }
}

function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 400) {
        throw new Exception("HTTP Error $httpCode: $response");
    }
    
    return $response;
}

testChatbotAPI();
?>
```

## 📚 SDK & Libraries

### JavaScript SDK
```javascript
class ChatbotAPI {
    constructor(apiKey, baseUrl = 'https://your-domain.com/api') {
        this.apiKey = apiKey;
        this.baseUrl = baseUrl;
        this.conversationId = null;
    }
    
    async sendMessage(message, options = {}) {
        const response = await fetch(`${this.baseUrl}/chatbot/message`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.apiKey}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message,
                conversation_id: this.conversationId,
                ...options
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            this.conversationId = data.data.conversation_id;
            return data.data;
        } else {
            throw new Error(data.message);
        }
    }
    
    async getConversation(conversationId) {
        const response = await fetch(`${this.baseUrl}/chatbot/conversation/${conversationId}`, {
            headers: {
                'Authorization': `Bearer ${this.apiKey}`
            }
        });
        
        return await response.json();
    }
    
    async healthCheck() {
        const response = await fetch(`${this.baseUrl}/chatbot/health`);
        return await response.json();
    }
}

// Usage
const chatbot = new ChatbotAPI('your-api-key');
const response = await chatbot.sendMessage('Hello!');
console.log(response.response);
```

### Python SDK
```python
import requests
import json
from typing import Optional, Dict, Any

class ChatbotAPI:
    def __init__(self, api_key: str, base_url: str = "https://your-domain.com/api"):
        self.api_key = api_key
        self.base_url = base_url
        self.conversation_id = None
        self.session = requests.Session()
        self.session.headers.update({
            'Authorization': f'Bearer {api_key}',
            'Content-Type': 'application/json'
        })
    
    def send_message(self, message: str, **options) -> Dict[Any, Any]:
        payload = {
            'message': message,
            'conversation_id': self.conversation_id,
            **options
        }
        
        response = self.session.post(
            f'{self.base_url}/chatbot/message',
            json=payload
        )
        
        data = response.json()
        
        if data['success']:
            self.conversation_id = data['data']['conversation_id']
            return data['data']
        else:
            raise Exception(data['message'])
    
    def get_conversation(self, conversation_id: str) -> Dict[Any, Any]:
        response = self.session.get(
            f'{self.base_url}/chatbot/conversation/{conversation_id}'
        )
        return response.json()
    
    def health_check(self) -> Dict[Any, Any]:
        response = requests.get(f'{self.base_url}/chatbot/health')
        return response.json()

# Usage
chatbot = ChatbotAPI('your-api-key')
response = chatbot.send_message('Hello!')
print(response['response'])
```

## 🔧 Advanced Features

### Streaming Responses
```javascript
async function* streamChatbotResponse(message) {
    const response = await fetch('/api/chatbot/message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'text/event-stream'
        },
        body: JSON.stringify({
            message: message,
            options: { stream: true }
        })
    });

    const reader = response.body.getReader();
    const decoder = new TextDecoder();

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value);
        const lines = chunk.split('\n');

        for (const line of lines) {
            if (line.startsWith('data: ')) {
                const data = JSON.parse(line.slice(6));
                yield data;
            }
        }
    }
}

// Usage
for await (const chunk of streamChatbotResponse('Tell me a story')) {
    console.log(chunk.content);
}
```

### WebSocket Integration
```javascript
class ChatbotWebSocket {
    constructor(url, apiKey) {
        this.ws = new WebSocket(`${url}?token=${apiKey}`);
        this.setupEventHandlers();
    }
    
    setupEventHandlers() {
        this.ws.onopen = () => {
            console.log('Connected to chatbot WebSocket');
        };
        
        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };
        
        this.ws.onclose = () => {
            console.log('Disconnected from chatbot WebSocket');
            // Implement reconnection logic
        };
    }
    
    sendMessage(message) {
        this.ws.send(JSON.stringify({
            type: 'message',
            data: { message }
        }));
    }
    
    handleMessage(data) {
        switch (data.type) {
            case 'response':
                console.log('Bot response:', data.data.response);
                break;
            case 'typing':
                console.log('Bot is typing...');
                break;
            case 'error':
                console.error('Error:', data.data.message);
                break;
        }
    }
}
```

## 📞 Support

Untuk bantuan teknis API:

- 📖 Documentation: [README.md](README.md)
- 🐛 Issues: GitHub Issues
- 💬 Chat: Widget support
- 📧 Email: api-support@your-domain.com

## 📝 Changelog

### v2.1.0 (2024-01-15)
- ✅ Added streaming responses
- ✅ Enhanced error handling
- ✅ Improved rate limiting
- ✅ WebSocket support

### v2.0.0 (2024-01-01)
- ✅ Complete API redesign
- ✅ Added conversation management
- ✅ RAG integration
- ✅ Authentication system

---

Happy coding! 🚀
