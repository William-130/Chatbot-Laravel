# 📚 RAG (Retrieval-Augmented Generation) Configuration Guide

## 🎯 Overview
RAG system mengintegrasikan chatbot dengan database website content untuk memberikan jawaban yang lebih akurat dan kontekstual menggunakan Gemini AI API.

## 🚀 Quick Start

### 1. Enable RAG di Widget
```javascript
// Di website Anda, sebelum load widget
window.ChatbotConfig = {
    rag: {
        enabled: true,        // Enable RAG
        apiUrl: '/api/chatbot/rag',  // RAG API endpoint
        autoManage: true     // Auto context management
    }
};

// Load widget
<script src="/js/chatbot-widget.js"></script>
```

### 2. Basic RAG Management via Console
```javascript
// Cek config RAG saat ini
await ChatbotWidget.rag.getConfig();

// Test RAG search
await ChatbotWidget.rag.testSearch("informasi jakarta");

// Enable/Disable RAG
ChatbotWidget.rag.enable();
ChatbotWidget.rag.disable();
```

## 🛠️ RAG Management Commands

### Via Artisan CLI
```bash
# Manage RAG websites
php artisan rag:manage

# Scrape website baru
php artisan scrape:website https://example.com

# Test chatbot dengan RAG
php artisan chatbot:test
```

### Via API Endpoints

#### Get RAG Configuration
```javascript
// GET /api/chatbot/rag/config
const response = await fetch('/api/chatbot/rag/config');
const data = await response.json();

// Response:
{
    "success": true,
    "websites": [
        {
            "id": 1,
            "name": "Metro Jakarta Maps",
            "url": "https://metro.jakarta.go.id",
            "description": "Peta dan informasi transportasi Jakarta",
            "is_active": true,
            "last_scraped_at": "2025-01-15T10:30:00.000000Z"
        }
    ],
    "stats": {
        "total_websites": 5,
        "active_websites": 4,
        "scraped_websites": 3
    }
}
```

#### Update Website Status
```javascript
// POST /api/chatbot/rag/config
const response = await fetch('/api/chatbot/rag/config', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        website_id: 1,
        is_active: true,
        description: "Updated description"
    })
});
```

#### Test RAG Search
```javascript
// POST /api/chatbot/rag/test
const response = await fetch('/api/chatbot/rag/test', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        query: "cara naik busway jakarta"
    })
});

// Response:
{
    "success": true,
    "query": "cara naik busway jakarta",
    "context_data": {
        "has_context": true,
        "website_name": "Metro Jakarta Maps",
        "relevance_score": 0.85,
        "search_method": "fulltext",
        "context": "Extracted relevant content..."
    }
}
```

## 📊 Website Management

### 1. Tambah Website Baru
```bash
php artisan rag:manage
# Pilih: Add new website
# Masukkan URL, nama, dan deskripsi
```

### 2. Scrape Content Website
```bash
php artisan scrape:website https://newwebsite.com
```

### 3. Update Status Website
```javascript
// Via widget console
await ChatbotWidget.rag.updateWebsite(1, {
    is_active: false,  // Disable website
    description: "Updated description"
});
```

### 4. Test Search Functionality
```javascript
// Test apakah RAG dapat menemukan content yang relevan
await ChatbotWidget.rag.testSearch("pertanyaan test");
```

## ⚙️ Advanced Configuration

### 1. Custom RAG Settings
```javascript
window.ChatbotConfig = {
    rag: {
        enabled: true,
        apiUrl: '/api/chatbot/rag',
        autoManage: true,
        // Advanced settings
        minRelevanceScore: 0.1,     // Minimum relevance threshold
        maxContextLength: 2000,     // Max context characters
        searchMethods: ['fulltext', 'relevance', 'keyword']
    },
    // Gemini API settings
    gemini: {
        temperature: 0.7,           // Response creativity
        maxTokens: 200,             // Response length limit
        timeout: 30000             // API timeout
    }
};
```

### 2. Website Categories
Sistem otomatis mengenali kategori website berdasarkan keywords:
- **Government**: jakarta, dki, pemda
- **Transport**: metro, busway, rute
- **Data**: data, statistik, dataset
- **Tech**: laravel, programming, api

### 3. Search Methods
RAG menggunakan 4 metode pencarian berurutan:
1. **Full-text Search**: MySQL FULLTEXT index
2. **Relevance Calculation**: Word matching algorithm
3. **Metadata Search**: Name, URL, description
4. **Keyword Category**: Predefined categories

## 🎛️ Widget Integration

### 1. Basic Integration
```html
<!DOCTYPE html>
<html>
<head>
    <title>Website dengan RAG Chatbot</title>
</head>
<body>
    <script>
        // Configure RAG before loading widget
        window.ChatbotConfig = {
            rag: {
                enabled: true
            }
        };
    </script>
    <script src="/js/chatbot-widget.js"></script>
</body>
</html>
```

### 2. Advanced Integration dengan Event Handling
```html
<script>
window.ChatbotConfig = {
    rag: {
        enabled: true,
        onContextFound: function(contextData) {
            console.log('RAG Context Found:', contextData);
        },
        onNoContext: function(query) {
            console.log('No RAG context for:', query);
        }
    }
};

// Handle widget ready event
document.addEventListener('chatbot-ready', function() {
    console.log('Chatbot with RAG ready!');
    
    // Get initial RAG config
    ChatbotWidget.rag.getConfig().then(config => {
        console.log('Available websites:', config.websites.length);
    });
});
</script>
```

## 🔧 Troubleshooting

### 1. RAG Tidak Menemukan Context
```javascript
// Check website status
await ChatbotWidget.rag.getConfig();

// Test search manually
await ChatbotWidget.rag.testSearch("test query");

// Check if website active
await ChatbotWidget.rag.updateWebsite(1, { is_active: true });
```

### 2. Content Tidak Up-to-date
```bash
# Re-scrape website
php artisan scrape:website https://website.com

# Check last scraped time
php artisan rag:manage
```

### 3. Gemini API Issues
```bash
# Check API key
php artisan tinker
>>> env('GEMINI_API_KEY')

# Test API connection
php artisan chatbot:test
```

### 4. Debug RAG Search
```javascript
// Enable debug mode
ChatbotWidget.debug();

// Test RAG step by step
await ChatbotWidget.rag.testSearch("debug query");
```

## 📈 Performance Optimization

### 1. Database Indexing
```sql
-- Pastikan FULLTEXT index aktif
SHOW INDEX FROM rag_websites WHERE Key_name = 'idx_fulltext_search';
```

### 2. Content Length Management
- Max context: 2000 characters
- Response limit: 400 characters (2-4 sentences)
- Relevance threshold: 0.1 minimum

### 3. Cache Management
```bash
# Clear application cache
php artisan cache:clear
php artisan config:clear
```

## 🚀 Production Deployment

### 1. Environment Variables
```env
GEMINI_API_KEY=your_gemini_api_key_here
DB_CONNECTION=mysql
APP_ENV=production
```

### 2. Database Migration
```bash
php artisan migrate
php artisan db:seed --class=RagWebsiteSeeder
```

### 3. Permission Setup
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### 4. Performance Monitoring
```javascript
// Monitor RAG performance
ChatbotWidget.rag.getConfig().then(config => {
    console.log('Active websites:', config.stats.active_websites);
    console.log('Scraped websites:', config.stats.scraped_websites);
});
```

## 📝 Example Usage

### Contoh Website untuk RAG:
1. **Portal Jakarta**: https://jakarta.go.id
2. **Metro Maps**: https://metro.jakarta.go.id  
3. **Data Portal**: https://data.jakarta.go.id
4. **Documentation**: https://docs.yoursite.com

### Contoh Pertanyaan yang Dijawab RAG:
- "Bagaimana cara naik busway di Jakarta?"
- "Informasi data statistik Jakarta"
- "Cara menggunakan metro maps"
- "Prosedur layanan pemerintah Jakarta"

---

## 🔗 Quick Links
- [Artisan Commands](artisan-commands.md)
- [API Documentation](api-docs.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Widget Integration](widget-integration.md)
