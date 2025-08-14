# 🤖 AI Chatbot Widget with RAG Technology

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow.svg)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)

AI Chatbot Widget yang dapat di-embed dengan teknologi RAG (Retrieval-Augmented Generation) untuk memberikan jawaban yang akurat berdasarkan konten website. Widget ini dilengkapi dengan fitur speech recognition, text-to-speech, avatar video, dan interface yang responsif.

## ✨ Fitur Utama

### 🎯 Core Features
- **RAG Technology**: Pencarian konten berdasarkan database website
- **Embeddable Widget**: Mudah di-embed ke website manapun
- **Responsive Design**: Mobile-friendly dan desktop-optimized
- **Real-time Chat**: Komunikasi instant dengan AI
- **Session Management**: Tracking percakapan per user

### 🎤 Voice Features
- **Speech Recognition**: Input suara untuk pertanyaan
- **Text-to-Speech**: AI berbicara dengan suara natural
- **Voice Controls**: Shortcut keyboard (Ctrl+Shift+V)
- **Multi-language Support**: Bahasa Indonesia dan English

### 🎬 Visual Features
- **Avatar Videos**: Animasi video berdasarkan status (idle, listening, thinking, speaking)
- **Fallback Icons**: Gradient background dengan emoji saat video tidak tersedia
- **Dark/Light Theme**: Toggle tema sesuai preferensi
- **Smooth Animations**: Transisi dan animasi yang halus

### 🔧 Technical Features
- **Auto-positioning**: Widget selalu berada di posisi yang tepat
- **Error Handling**: Penanganan error yang robust
- **Debug Console**: Tools untuk debugging dan testing
- **Cross-browser Support**: Kompatibel dengan browser modern

## 🚀 Instalasi dan Setup

### Persyaratan Sistem
- PHP 8.3 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Composer 2.x
- Node.js 18+ (opsional, untuk development)

### 1. Clone Repository
```bash
git clone <repository-url>
cd chatbot-widget
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (opsional)
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatbot_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Database Migration
```bash
# Run migrations
php artisan migrate

# Seed sample data (opsional)
php artisan db:seed
```

### 6. Start Server
```bash
# Development server
php artisan serve

# Production (dengan web server seperti Apache/Nginx)
# Point document root ke /public folder
```

## 📁 Struktur Project

```
chatbot-widget/
├── app/
│   ├── Http/Controllers/     # API Controllers
│   └── Models/              # Database Models
├── config/                  # Laravel Configuration
├── database/
│   ├── migrations/          # Database Migrations
│   └── seeders/            # Sample Data
├── public/
│   ├── js/
│   │   └── chatbot-widget.js    # Main Widget File
│   ├── css/                 # Styling Files
│   └── videos/
│       └── avatar/          # Avatar Video Files
├── resources/views/         # Blade Templates
├── routes/                  # API Routes
└── storage/                 # Storage & Logs
```

## 🎯 Cara Embed Widget

### Method 1: CDN Style (Recommended)
```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website</title>
</head>
<body>
    <!-- Your website content -->
    
    <!-- Chatbot Widget -->
    <script>
        // Optional: Configure widget before loading
        window.ChatbotConfig = {
            apiUrl: 'https://your-domain.com/api/chatbot/message',
            position: 'bottom-right', // 'bottom-left' or 'bottom-right'
            title: 'Customer Support',
            subtitle: 'We\'re here to help!',
            theme: 'auto', // 'light', 'dark', or 'auto'
            soundEnabled: true,
            voiceEnabled: true,
            language: 'id-ID'
        };
    </script>
    <script src="https://your-domain.com/js/chatbot-widget.js"></script>
</body>
</html>
```

### Method 2: Direct Embed
```html
<!-- Download chatbot-widget.js dan host di server Anda -->
<script src="/path/to/chatbot-widget.js"></script>
```

### Method 3: Dynamic Loading
```javascript
// Load widget dynamically
function loadChatbot() {
    const script = document.createElement('script');
    script.src = 'https://your-domain.com/js/chatbot-widget.js';
    script.onload = function() {
        console.log('Chatbot widget loaded!');
    };
    document.head.appendChild(script);
}

// Load after page ready
window.addEventListener('load', loadChatbot);
```

## ⚙️ Konfigurasi Widget

### Basic Configuration
```javascript
window.ChatbotConfig = {
    // API Endpoint
    apiUrl: '/api/chatbot/message',
    
    // Widget Position
    position: 'bottom-right', // 'bottom-left' | 'bottom-right'
    
    // Appearance
    title: 'AI Assistant',
    subtitle: 'Online & Ready',
    theme: 'auto', // 'light' | 'dark' | 'auto'
    
    // Features
    soundEnabled: true,
    voiceEnabled: true,
    language: 'id-ID',
    
    // Performance
    requestTimeout: 30000,
    maxRetries: 3,
    
    // Avatar Configuration
    avatar: {
        enabled: true,
        basePath: '/videos/avatar/',
        files: {
            idle: 'idle.mp4',
            listening: 'listening.mp4',
            thinking: 'thinking.mp4',
            speaking: 'speaking.mp4'
        }
    }
};
```

## 🛠️ Command Line Interface

### RAG Website Management

```bash
# List all websites in RAG database
php artisan rag:manage list

# Add new website to RAG database
php artisan rag:manage add --url="https://example.com" --name="Website Name" --description="Description"

# Update existing website
php artisan rag:manage update --id=1 --name="New Name"

# Delete website
php artisan rag:manage delete --id=1

# Enable/disable website
php artisan rag:manage enable --id=1
php artisan rag:manage disable --id=1

# Show website details
php artisan rag:manage show --id=1
```

### Website Scraping

```bash
# Scrape specific website
php artisan scrape:website "https://example.com" --name="Website Name" --description="Description"

# Update existing website content
php artisan scrape:website "https://example.com" --update

# Re-scrape all active websites
php artisan scrape:website --all
```

### Chatbot Testing

```bash
# Test single message
php artisan chatbot:test "Your question here"

# Interactive chat session
php artisan chatbot:test --interactive

# Test with specific message
php artisan chatbot:test "Apa itu Laravel framework?"
```

### Examples

#### Complete workflow: Add and scrape a website
```bash
# 1. Add website to database
php artisan rag:manage add \
  --url="https://laravel.com/docs" \
  --name="Laravel Documentation" \
  --description="Official Laravel framework documentation"

# 2. Scrape content
php artisan scrape:website "https://laravel.com/docs" --update

# 3. Test chatbot with relevant question
php artisan chatbot:test "How to install Laravel?"

# 4. Check website status
php artisan rag:manage show --id=1
```

#### Batch operations
```bash
# Re-scrape all websites
php artisan scrape:website --all

# List all websites with status
php artisan rag:manage list
```

### Advanced Configuration
```javascript
// Custom styling
window.ChatbotConfig = {
    // ... basic config
    
    // Custom CSS variables
    customCSS: {
        '--primary-color': '#3b82f6',
        '--secondary-color': '#8b5cf6',
        '--background-color': '#ffffff',
        '--text-color': '#111827'
    },
    
    // Custom messages
    messages: {
        welcomeMessage: 'Halo! Ada yang bisa saya bantu?',
        offlineMessage: 'Maaf, layanan sedang offline.',
        errorMessage: 'Terjadi kesalahan. Silakan coba lagi.'
    }
};
```

## 💾 Database RAG - Menambah Context

### Struktur Database

#### Tabel `rag_websites`
```sql
CREATE TABLE rag_websites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(500) NOT NULL,
    title VARCHAR(500),
    content LONGTEXT,
    metadata JSON,
    embedding_vector JSON,
    last_crawled TIMESTAMP,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_url (url(255)),
    INDEX idx_status (status),
    FULLTEXT(title, content)
);
```

#### Tabel `conversations`
```sql
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_message TEXT,
    bot_response TEXT,
    context_used JSON,
    response_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_created (created_at)
);
```

### Menambah Context RAG

#### 1. Via Database Seeder
```php
// database/seeders/RagWebsiteSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RagWebsite;

class RagWebsiteSeeder extends Seeder
{
    public function run()
    {
        $websites = [
            [
                'url' => 'https://example.com/about',
                'title' => 'Tentang Perusahaan',
                'content' => 'Perusahaan kami didirikan pada tahun 2020...',
                'metadata' => json_encode([
                    'category' => 'about',
                    'language' => 'id',
                    'tags' => ['company', 'about', 'history']
                ])
            ],
            [
                'url' => 'https://example.com/products',
                'title' => 'Produk dan Layanan',
                'content' => 'Kami menyediakan berbagai produk berkualitas...',
                'metadata' => json_encode([
                    'category' => 'products',
                    'language' => 'id',
                    'tags' => ['products', 'services']
                ])
            ]
        ];

        foreach ($websites as $website) {
            RagWebsite::create($website);
        }
    }
}
```

#### 2. Via API Endpoint
```php
// app/Http/Controllers/RagController.php
<?php

namespace App\Http\Controllers;

use App\Models\RagWebsite;
use Illuminate\Http\Request;

class RagController extends Controller
{
    public function addContext(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'title' => 'required|string|max:500',
            'content' => 'required|string',
            'metadata' => 'nullable|array'
        ]);

        $ragWebsite = RagWebsite::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Context added successfully',
            'data' => $ragWebsite
        ]);
    }
}
```

#### 3. Via Artisan Command
```php
// app/Console/Commands/AddRagContext.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RagWebsite;

class AddRagContext extends Command
{
    protected $signature = 'rag:add {url} {title} {content}';
    protected $description = 'Add new RAG context to database';

    public function handle()
    {
        $url = $this->argument('url');
        $title = $this->argument('title');
        $content = $this->argument('content');

        $context = RagWebsite::create([
            'url' => $url,
            'title' => $title,
            'content' => $content,
            'status' => 'active'
        ]);

        $this->info("RAG context added with ID: {$context->id}");
    }
}
```

#### 4. Bulk Import dari CSV
```php
// app/Console/Commands/ImportRagCsv.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RagWebsite;
use League\Csv\Reader;

class ImportRagCsv extends Command
{
    protected $signature = 'rag:import {file}';
    protected $description = 'Import RAG contexts from CSV file';

    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();
        $count = 0;

        foreach ($records as $record) {
            RagWebsite::create([
                'url' => $record['url'],
                'title' => $record['title'],
                'content' => $record['content'],
                'metadata' => json_encode([
                    'category' => $record['category'] ?? 'general',
                    'tags' => explode(',', $record['tags'] ?? '')
                ]),
                'status' => 'active'
            ]);
            $count++;
        }

        $this->info("Imported {$count} RAG contexts successfully");
    }
}
```

### Format CSV untuk Import
```csv
url,title,content,category,tags
https://example.com/faq,FAQ,Pertanyaan yang sering ditanyakan...,faq,"faq,help,support"
https://example.com/contact,Kontak,Hubungi kami di...,contact,"contact,support"
```

### Contoh Penggunaan

#### 1. Tambah Context Manual
```bash
php artisan rag:add "https://example.com/services" "Layanan Kami" "Kami menyediakan layanan konsultasi IT..."
```

#### 2. Import dari CSV
```bash
php artisan rag:import storage/app/rag_contexts.csv
```

#### 3. Seed Sample Data
```bash
php artisan db:seed --class=RagWebsiteSeeder
```

## 🧪 Testing dan Debugging

### Widget Testing
```javascript
// Test di browser console
ChatbotWidget.debug()                    // Info widget
ChatbotWidget.testSpeechRecognition()    // Test speech recognition
ChatbotWidget.testMicrophone()           // Test mikrofon
ChatbotWidget.testAvatarVideos()         // Test avatar videos
```

### API Testing
```bash
# Test chatbot endpoint
curl -X POST http://localhost:8000/api/chatbot/message \
  -H "Content-Type: application/json" \
  -d '{"message": "Halo", "session_id": "test123"}'
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Widget tidak muncul
```javascript
// Check di console
console.log(window.ChatbotWidget);
ChatbotWidget.debug();
```

#### 2. Speech Recognition tidak bekerja
- Pastikan menggunakan HTTPS (required untuk microphone access)
- Check browser support
- Pastikan izin mikrofon telah diberikan

#### 3. Avatar video tidak muncul
- Check path video file
- Pastikan file video ada di folder `public/videos/avatar/`
- Check console untuk error loading

#### 4. API Error
- Check Laravel logs di `storage/logs/`
- Pastikan database connection
- Verify API endpoints

## 📝 API Documentation

### POST /api/chatbot/message
Send message to chatbot and get AI response.

**Request:**
```json
{
    "message": "Halo, apa kabar?",
    "session_id": "session_123456789"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Halo! Saya baik-baik saja. Ada yang bisa saya bantu?",
    "context_used": [
        {
            "url": "https://example.com/about",
            "title": "Tentang Kami",
            "relevance_score": 0.85
        }
    ],
    "response_time": 1250
}
```

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License. See [LICENSE](LICENSE) file for details.

## 👥 Support

- 📧 Email: support@example.com
- 💬 Discord: [Join our community](https://discord.gg/example)
- 📖 Documentation: [Full docs](https://docs.example.com)
- 🐛 Bug Reports: [GitHub Issues](https://github.com/example/issues)

## 🙏 Acknowledgments

- Laravel Framework
- OpenAI API
- Speech Recognition API
- Contributors and testers

---

Made with ❤️ by [Your Team Name](https://example.com)
