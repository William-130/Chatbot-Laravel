# 📦 Widget Embed Guide

Panduan lengkap untuk meng-embed AI Chatbot Widget ke website Anda.

## 🚀 Quick Start

### 1. Basic Embed (Minimal Setup)
```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website</title>
</head>
<body>
    <!-- Your content here -->
    
    <!-- Chatbot Widget - Add this before closing </body> tag -->
    <script src="https://your-chatbot-domain.com/js/chatbot-widget.js"></script>
</body>
</html>
```

### 2. Configured Embed (Recommended)
```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website with Chatbot</title>
</head>
<body>
    <!-- Your content here -->
    
    <!-- Configure widget before loading -->
    <script>
        window.ChatbotConfig = {
            apiUrl: 'https://your-chatbot-domain.com/api/chatbot/message',
            title: 'Customer Support',
            subtitle: 'How can we help you today?',
            position: 'bottom-right',
            theme: 'auto'
        };
    </script>
    
    <!-- Load the widget -->
    <script src="https://your-chatbot-domain.com/js/chatbot-widget.js"></script>
</body>
</html>
```

## ⚙️ Configuration Options

### Complete Configuration
```javascript
window.ChatbotConfig = {
    // API Configuration
    apiUrl: 'https://your-domain.com/api/chatbot/message',
    requestTimeout: 30000,
    maxRetries: 3,
    
    // Widget Appearance
    title: 'AI Assistant',
    subtitle: 'Online & Ready',
    position: 'bottom-right', // 'bottom-left' | 'bottom-right'
    theme: 'auto', // 'light' | 'dark' | 'auto'
    
    // Features
    soundEnabled: true,
    voiceEnabled: true,
    language: 'id-ID', // 'en-US' | 'id-ID'
    
    // Voice Configuration
    voiceRate: 0.9,
    voicePitch: 1,
    
    // Animation
    animationDuration: 300,
    
    // Avatar Videos
    avatar: {
        enabled: true,
        basePath: '\videos\avatar\',
        files: {
            idle: 'idle.mp4',
            listening: 'listening.mp4',
            thinking: 'thinking.mp4',
            speaking: 'speaking.mp4'
        }
    }
};
```

### Position Options
```javascript
// Bottom Right (Default)
window.ChatbotConfig = {
    position: 'bottom-right'
};

// Bottom Left
window.ChatbotConfig = {
    position: 'bottom-left'
};
```

### Theme Options
```javascript
// Auto (follows system preference)
window.ChatbotConfig = {
    theme: 'auto'
};

// Always Light Theme
window.ChatbotConfig = {
    theme: 'light'
};

// Always Dark Theme
window.ChatbotConfig = {
    theme: 'dark'
};
```

## 🎨 Custom Styling

### Override Default Colors
```html
<style>
    :root {
        --chatbot-primary: #your-brand-color;
        --chatbot-secondary: #your-secondary-color;
        --chatbot-background: #ffffff;
        --chatbot-text: #333333;
    }
</style>
```

### Custom CSS Classes
```css
/* Widget Container */
.chatbot-widget {
    /* Your custom styles */
}

/* Toggle Button */
.chatbot-toggle-btn {
    /* Your custom styles */
}

/* Popup Window */
.chatbot-popup {
    /* Your custom styles */
}

/* Messages */
.chatbot-message {
    /* Your custom styles */
}
```

## 🎯 Advanced Integration

### 1. Dynamic Loading
```javascript
// Load widget after page is ready
function loadChatbot() {
    if (window.ChatbotWidget) {
        console.log('Chatbot already loaded');
        return;
    }
    
    const script = document.createElement('script');
    script.src = 'https://your-domain.com/js/chatbot-widget.js';
    script.onload = function() {
        console.log('Chatbot widget loaded successfully');
    };
    script.onerror = function() {
        console.error('Failed to load chatbot widget');
    };
    
    document.head.appendChild(script);
}

// Load when page is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadChatbot);
} else {
    loadChatbot();
}
```

### 2. Conditional Loading
```javascript
// Only load on certain pages
function shouldLoadChatbot() {
    const currentPath = window.location.pathname;
    const excludePaths = ['/admin', '/login', '/checkout'];
    
    return !excludePaths.some(path => currentPath.startsWith(path));
}

if (shouldLoadChatbot()) {
    // Load chatbot
    const script = document.createElement('script');
    script.src = 'https://your-domain.com/js/chatbot-widget.js';
    document.head.appendChild(script);
}
```

### 3. User-based Configuration
```javascript
// Different config for different user types
const userType = getUserType(); // Your function to get user type

let chatbotConfig = {
    apiUrl: 'https://your-domain.com/api/chatbot/message',
    title: 'Support',
    position: 'bottom-right'
};

if (userType === 'premium') {
    chatbotConfig.title = 'Premium Support';
    chatbotConfig.voiceEnabled = true;
    chatbotConfig.avatar.enabled = true;
} else if (userType === 'guest') {
    chatbotConfig.title = 'Guest Support';
    chatbotConfig.voiceEnabled = false;
    chatbotConfig.avatar.enabled = false;
}

window.ChatbotConfig = chatbotConfig;
```

## 🔌 JavaScript API

### Widget Control
```javascript
// Check if widget is loaded
if (window.ChatbotWidget) {
    
    // Open/Close widget
    ChatbotWidget.toggle();
    
    // Send message programmatically
    ChatbotWidget.sendMessage('Hello from JavaScript!');
    
    // Change theme
    ChatbotWidget.toggleTheme();
    
    // Toggle sound
    ChatbotWidget.toggleSound();
    
    // Set state
    ChatbotWidget.setState('thinking');
    
}
```

### Event Listeners
```javascript
// Listen for widget events (if available)
document.addEventListener('chatbot:ready', function() {
    console.log('Chatbot widget is ready');
});

document.addEventListener('chatbot:message-sent', function(event) {
    console.log('Message sent:', event.detail.message);
});

document.addEventListener('chatbot:message-received', function(event) {
    console.log('Response received:', event.detail.response);
});
```

## 📱 Mobile Optimization

### Responsive Configuration
```javascript
window.ChatbotConfig = {
    // Mobile-specific settings
    position: window.innerWidth <= 768 ? 'bottom-right' : 'bottom-right',
    
    // Disable video on mobile for performance
    avatar: {
        enabled: window.innerWidth > 768,
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

### Touch-Friendly Configuration
```css
/* Larger touch targets on mobile */
@media (max-width: 768px) {
    .chatbot-toggle-btn {
        width: 60px !important;
        height: 60px !important;
    }
    
    .chatbot-action-btn {
        min-height: 44px !important;
    }
}
```

## 🔒 Security Considerations

### 1. CSP (Content Security Policy)
```html
<meta http-equiv="Content-Security-Policy" 
      content="script-src 'self' https://your-chatbot-domain.com; 
               style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; 
               font-src https://fonts.gstatic.com;">
```

### 2. CORS Configuration
Ensure your chatbot API server allows requests from your website domain.

### 3. API Key Protection
```javascript
// Don't expose API keys in client-side code
// Use your backend as proxy if needed
window.ChatbotConfig = {
    apiUrl: '/api/chatbot-proxy', // Your backend proxy
    // Don't put API keys here
};
```

## 🐛 Troubleshooting

### Common Issues

#### 1. Widget not appearing
```javascript
// Debug in console
console.log('ChatbotWidget available:', !!window.ChatbotWidget);
if (window.ChatbotWidget) {
    ChatbotWidget.debug();
}
```

#### 2. CORS errors
- Ensure your chatbot server allows your domain
- Check browser console for specific CORS errors
- Consider using a proxy endpoint

#### 3. Mixed content warnings (HTTP/HTTPS)
- Ensure widget is loaded via HTTPS if your site uses HTTPS
- Update apiUrl to use HTTPS

#### 4. Script loading errors
```javascript
// Add error handling
const script = document.createElement('script');
script.src = 'https://your-domain.com/js/chatbot-widget.js';
script.onerror = function() {
    console.error('Failed to load chatbot widget');
    // Fallback: show contact form or other support option
    showFallbackSupport();
};
```

## 🧪 Testing

### Local Testing
```html
<!-- For local development -->
<script>
    window.ChatbotConfig = {
        apiUrl: 'http://localhost:8000/api/chatbot/message',
        // ... other config
    };
</script>
<script src="http://localhost:8000/js/chatbot-widget.js"></script>
```

### Production Testing
```javascript
// Test all features
if (window.ChatbotWidget) {
    ChatbotWidget.testSpeechRecognition();
    ChatbotWidget.testMicrophone();
    ChatbotWidget.testAvatarVideos();
}
```

## 📊 Analytics Integration

### Google Analytics
```javascript
// Track chatbot interactions
document.addEventListener('chatbot:message-sent', function(event) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'chatbot_message_sent', {
            'event_category': 'Chatbot',
            'event_label': 'User Message'
        });
    }
});
```

### Custom Analytics
```javascript
// Your analytics service
document.addEventListener('chatbot:ready', function() {
    analytics.track('Chatbot Loaded');
});

document.addEventListener('chatbot:message-sent', function(event) {
    analytics.track('Chatbot Message Sent', {
        message_length: event.detail.message.length
    });
});
```

## 🔄 Updates and Versioning

### Automatic Updates
```javascript
// Always load latest version
const script = document.createElement('script');
script.src = 'https://your-domain.com/js/chatbot-widget.js?v=' + Date.now();
```

### Version Pinning (Recommended for Production)
```javascript
// Pin to specific version for stability
const version = '2.1.0';
const script = document.createElement('script');
script.src = `https://your-domain.com/js/chatbot-widget-${version}.js`;
```

## 💡 Best Practices

1. **Load widget asynchronously** to avoid blocking page load
2. **Test on multiple devices** and browsers
3. **Monitor performance** impact on your website
4. **Provide fallback** support options if widget fails
5. **Configure CSP** properly for security
6. **Use HTTPS** for voice features
7. **Test with slow connections** to ensure good UX
8. **Monitor API usage** and set appropriate rate limits

## 📞 Support

If you need help with widget integration:

- 📖 Check the main [README.md](README.md)
- 🐛 Report issues on GitHub
- 💬 Join our Discord community
- 📧 Email: support@your-domain.com

---

Happy embedding! 🚀
