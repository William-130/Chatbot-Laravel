# 🎯 Hybrid AI Chatbot Configuration

## 🧠 Model Behavior: Smart Hybrid Mode

Chatbot sekarang dapat menjawab **pertanyaan umum** dan **pertanyaan kontekstual** secara otomatis!

## 🔄 How It Works

### 1. **Question Analysis**
Sistem otomatis menganalisis pertanyaan untuk menentukan:
- ❓ **General Question**: Pertanyaan umum (sains, teknologi, tips, dll)
- 🎯 **Contextual Question**: Pertanyaan yang membutuhkan info spesifik dari database

### 2. **Response Strategy**

#### 📚 **High Relevance Context** (Score > 0.5)
```
PRIORITAS: Database Context + General Knowledge
EXAMPLE: "Cara naik busway jakarta" → Gunakan info Metro Jakarta + pengetahuan umum transportasi
```

#### 📖 **Medium Relevance Context** (Score 0.1-0.5)  
```
PRIORITAS: General Knowledge + Database Context
EXAMPLE: "Tips transportasi umum" → Pengetahuan umum + referensi database jika ada
```

#### 🌐 **No Context / General Question**
```
PRIORITAS: Enhanced General Knowledge
EXAMPLE: "Apa itu programming?" → Murni pengetahuan umum yang komprehensif
```

## ⚙️ Configuration Options

### 1. **Widget Configuration**
```javascript
window.ChatbotConfig = {
    rag: {
        enabled: true,              // Enable RAG system
        mode: 'hybrid',            // hybrid | context_only | general_only
        generalKnowledge: true,    // Allow general knowledge responses
        fallbackToGeneral: true,   // Fallback to general when no context
        relevanceThreshold: 0.1    // Minimum relevance score
    }
};
```

### 2. **Server Configuration** (Environment)
```env
# RAG Behavior Settings
RAG_MODE=hybrid                    # hybrid | context_only | general_only
RAG_GENERAL_KNOWLEDGE=true        # Enable general knowledge responses
RAG_RELEVANCE_THRESHOLD=0.1       # Minimum context relevance
RAG_FALLBACK_ENABLED=true         # Allow fallback to general knowledge
```

### 3. **Runtime Configuration**
```javascript
// Enable/disable modes via console
ChatbotWidget.rag.setMode('hybrid');        // Smart hybrid mode
ChatbotWidget.rag.setMode('context_only');  // Only use database context
ChatbotWidget.rag.setMode('general_only');  // Only general knowledge

// Check current mode
ChatbotWidget.rag.getMode();  // Returns: 'hybrid'
```

## 🎭 Response Examples

### General Knowledge Questions:
```
USER: "Apa itu artificial intelligence?"
BOT: "Artificial Intelligence (AI) adalah teknologi yang memungkinkan mesin melakukan tugas yang biasanya memerlukan kecerdasan manusia, seperti belajar, reasoning, dan pemecahan masalah. AI digunakan dalam berbagai aplikasi seperti chatbot, mobil otonom, dan sistem rekomendasi."

USER: "Tips belajar programming untuk pemula?"
BOT: "Untuk pemula, mulai dengan bahasa yang mudah seperti Python atau JavaScript. Praktek coding setiap hari, buat project kecil, dan jangan takut membuat error karena itu bagian dari proses belajar. Bergabunglah dengan komunitas developer untuk saling belajar."
```

### Context-Based Questions:
```
USER: "Bagaimana cara naik busway di Jakarta?"
BOT: "Berdasarkan info Metro Jakarta, untuk naik Transjakarta: beli tiket di loket/aplikasi, tap kartu di gate, tunggu bus di halte yang benar, dan tap lagi saat turun. Pastikan cek rute di aplikasi Transjakarta untuk jalur yang tepat. (Sumber: Metro Jakarta Maps)"

USER: "Data statistik penduduk Jakarta?"
BOT: "Menurut data portal Jakarta, DKI Jakarta memiliki populasi sekitar 10.5 juta jiwa dengan kepadatan tinggi. Data lengkap tersedia di portal data.jakarta.go.id untuk statistik demografi, ekonomi, dan sosial terkini. (Sumber: Portal Data Jakarta)"
```

### Hybrid Questions:
```
USER: "Apa keuntungan transportasi umum?"
BOT: "Transportasi umum memiliki banyak keuntungan: mengurangi kemacetan, lebih hemat biaya, ramah lingkungan, dan mengurangi stres berkendara. Di Jakarta misalnya, Transjakarta dan MRT menjadi pilihan efisien untuk mobilitas warga dengan akses ke berbagai titik strategis kota."
```

## 🛠️ Advanced Configuration

### Custom Question Patterns
```javascript
// Add custom general question patterns
ChatbotWidget.rag.addGeneralPatterns([
    /\b(tutorial|panduan|cara)\s.*\b(umum|dasar|basic)\b/i,
    /\b(pengertian|definisi|arti)\s.*\b/i
]);

// Add context-priority patterns  
ChatbotWidget.rag.addContextPatterns([
    /\b(jakarta|dki|metro|busway)\b/i,
    /\b(data|statistik|informasi)\s.*\b(resmi|pemerintah)\b/i
]);
```

### Relevance Scoring Adjustment
```javascript
// Adjust scoring thresholds
ChatbotWidget.rag.config({
    highRelevanceThreshold: 0.7,    // Strongly prioritize context
    mediumRelevanceThreshold: 0.3,  // Balance context + general
    lowRelevanceThreshold: 0.1      // Fallback to general
});
```

### Response Behavior Tuning
```javascript
// Fine-tune response generation
ChatbotWidget.rag.responseConfig({
    maxContextLength: 2000,         // Max chars from context
    maxResponseLength: 400,         // Max response length  
    citeSources: true,              // Always cite sources
    combineKnowledge: true,         // Mix context + general knowledge
    fallbackTimeout: 5000           // Fallback timeout
});
```

## 📊 Monitoring & Analytics

### Response Analysis
```javascript
// Get response statistics
await ChatbotWidget.rag.getStats();
/* Returns:
{
    totalQuestions: 150,
    generalKnowledgeResponses: 89,
    contextBasedResponses: 45,
    hybridResponses: 16,
    averageRelevanceScore: 0.34
}
*/

// Get recent response types
await ChatbotWidget.rag.getRecentResponses();
```

### Performance Monitoring
```javascript
// Monitor RAG performance
ChatbotWidget.rag.monitor({
    logGeneralQuestions: true,
    logContextMatches: true,
    logResponseTimes: true,
    logUserSatisfaction: true
});
```

## 🎯 Best Practices

### 1. **Content Strategy**
- ✅ Add websites dengan konten spesifik dan unik
- ✅ Pastikan deskripsi website yang jelas
- ✅ Update content secara berkala
- ✅ Monitor relevance score untuk optimasi

### 2. **Question Handling**
- ✅ Let AI decide antara general vs contextual
- ✅ Provide fallback untuk questions tidak terjawab
- ✅ Combine context dengan general knowledge when appropriate
- ✅ Always cite sources untuk context-based answers

### 3. **User Experience**
- ✅ Keep responses concise (2-4 sentences)
- ✅ Provide follow-up suggestions
- ✅ Use natural, conversational language
- ✅ Balance accuracy dengan readability

## 🚀 Quick Setup Commands

```bash
# Test hybrid responses
php artisan chatbot:test

# Check RAG configuration
php artisan rag:manage

# Add new knowledge source
php artisan scrape:website https://newsite.com

# Monitor performance
tail -f storage/logs/laravel.log | grep "RAG"
```

## 📈 Success Metrics

- **Context Utilization Rate**: Berapa persen questions menggunakan context
- **User Satisfaction**: Rating responses berdasarkan user feedback  
- **Response Accuracy**: Manual review untuk accuracy check
- **Coverage Rate**: Berapa persen questions bisa dijawab dengan baik

---

**Result**: Chatbot yang bisa menjawab **SEMUA JENIS PERTANYAAN** - dari yang umum sampai yang sangat spesifik! 🎉
