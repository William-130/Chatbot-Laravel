/*!
 * AI Chatbot Widget - Standalone Embeddable Version
 * Enhanced with all features from chatbot-enhanced.blade.php
 * Version: 2.0.1 - Fixed Avatar Integration
 * Author: AI Assistant Team
 */

(function() {
    'use strict';

    // Prevent multiple instances
    if (window.ChatbotWidget) {
        console.warn('Chatbot widget already loaded');
        return;
    }

    // Configuration with defaults
    const defaultConfig = {
        apiUrl: '/api/chatbot/message',
        position: 'bottom-right',
        title: 'AI Assistant',
        subtitle: 'Online & Ready',
        theme: 'auto', // auto, light, dark
        soundEnabled: true,
        voiceEnabled: true,
        language: 'id-ID',
        maxRetries: 3,
        requestTimeout: 30000,
        voiceRate: 0.9,
        voicePitch: 1,
        animationDuration: 300,
        // Avatar configuration - Fixed paths
        avatar: {
            enabled: true,
            basePath: '\public\videos\avatar', // Fixed path
            files: {
                idle: 'idle.mp4',
                listening: 'listening.mp4',
                thinking: 'thinking.mp4',
                speaking: 'speaking.mp4'
            }
        }
    };

    // Merge user config with defaults
    const config = Object.assign({}, defaultConfig, window.ChatbotConfig || {});

    // State management
    let state = {
        current: 'idle',
        isProcessing: false,
        isWidgetOpen: false,
        isMinimized: false,
        isDarkMode: localStorage.getItem('chatbot-darkMode') === 'true',
        isSoundEnabled: localStorage.getItem('chatbot-soundEnabled') !== 'false',
        sessionId: `session_${Date.now()}`
    };

    // DOM elements cache
    let elements = {};

    // Speech objects
    let recognition = null;
    let currentUtterance = null;

    // CSS styles for the widget (optimized)
    const widgetStyles = `
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .chatbot-widget * {
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        .chatbot-widget {
            position: fixed !important;
            z-index: 999999 !important;
            font-family: 'Inter', sans-serif;
            pointer-events: none;
        }

        .chatbot-widget * { pointer-events: auto; }

        .chatbot-widget.bottom-right {
            bottom: 20px !important;
            right: 20px !important;
            top: auto !important;
            left: auto !important;
        }

        .chatbot-widget.bottom-left {
            bottom: 20px !important;
            left: 20px !important;
            top: auto !important;
            right: auto !important;
        }

        .chatbot-toggle-btn {
            width: 64px !important;
            height: 64px !important;
            border-radius: 50% !important;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%) !important;
            border: none !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            position: relative !important;
            z-index: 999999 !important;
        }

        .chatbot-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .chatbot-popup {
            position: absolute !important;
            bottom: 80px !important;
            right: 0 !important;
            width: 384px !important;
            height: 600px !important;
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transform: scale(0.9) translateY(20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(229, 231, 235, 1);
            max-width: calc(100vw - 40px);
            max-height: calc(100vh - 120px);
        }

        .chatbot-widget.bottom-left .chatbot-popup {
            right: auto !important;
            left: 0 !important;
        }

        .chatbot-popup.open {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .chatbot-popup.dark {
            background: #1f2937;
            border-color: #374151;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            padding: 16px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .chatbot-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255, 255, 255, 0.05) 10px, rgba(255, 255, 255, 0.05) 20px);
            pointer-events: none;
        }

        .chatbot-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chatbot-avatar-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chatbot-avatar, .chatbot-main-avatar {
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .chatbot-avatar {
            width: 40px;
            height: 40px;
        }

        .chatbot-main-avatar {
            width: 120px;
            height: 120px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border: 4px solid rgba(255, 255, 255, 0.5);
        }

        .chatbot-avatar video, .chatbot-main-avatar video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Avatar fallback styling */
        .avatar-fallback {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .chatbot-avatar .avatar-fallback {
            font-size: 14px;
        }

        .chatbot-main-avatar .avatar-fallback {
            font-size: 32px;
        }

        .chatbot-info h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .chatbot-info p {
            margin: 0;
            font-size: 12px;
            opacity: 0.8;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chatbot-controls {
            display: flex;
            gap: 8px;
        }

        .chatbot-control-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chatbot-control-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .chatbot-body {
            display: flex;
            flex-direction: column;
            height: 520px;
        }

        .chatbot-avatar-display {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px;
            background: linear-gradient(to bottom, #f8fafc, white);
            border-bottom: 1px solid #e5e7eb;
        }

        .chatbot-popup.dark .chatbot-avatar-display {
            background: linear-gradient(to bottom, #374151, #1f2937);
            border-bottom-color: #4b5563;
        }

        .chatbot-messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            background: white;
        }

        .chatbot-popup.dark .chatbot-messages {
            background: #1f2937;
        }

        .chatbot-message {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
            animation: slideUp 0.3s ease-out;
            color: #374151;
        }

        .chatbot-popup.dark .chatbot-message {
            background: #374151;
            color: #d1d5db;
        }

        .chatbot-input-area {
            flex-shrink: 0;
            padding: 16px;
            border-top: 1px solid #e5e7eb;
            background: white;
        }

        .chatbot-popup.dark .chatbot-input-area {
            border-top-color: #4b5563;
            background: #1f2937;
        }

        .chatbot-input-container {
            position: relative;
            margin-bottom: 12px;
        }

        .chatbot-input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #111827;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .chatbot-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .chatbot-popup.dark .chatbot-input {
            background: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .chatbot-popup.dark .chatbot-input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        .chatbot-char-count {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: #6b7280;
        }

        .chatbot-char-count.warning {
            color: #ef4444;
        }

        .chatbot-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .chatbot-action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .chatbot-voice-btn {
            background: #3b82f6;
            color: white;
        }

        .chatbot-voice-btn:hover {
            background: #2563eb;
        }

        .chatbot-voice-btn.listening {
            background: #ef4444;
            animation: pulse 1s infinite;
        }

        .chatbot-send-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
            color: white;
        }

        .chatbot-send-btn:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
        }

        .chatbot-send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .chatbot-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .chatbot-quick-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            background: #f3f4f6;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chatbot-quick-btn:hover {
            background: #e5e7eb;
        }

        .chatbot-popup.dark .chatbot-quick-btn {
            background: #4b5563;
            color: #d1d5db;
        }

        .chatbot-popup.dark .chatbot-quick-btn:hover {
            background: #6b7280;
        }

        .chatbot-clear-btn {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .chatbot-clear-btn:hover {
            background: #fee2e2;
        }

        .chatbot-popup.dark .chatbot-clear-btn {
            background: #7f1d1d;
            color: #fca5a5;
            border-color: #991b1b;
        }

        .chatbot-popup.dark .chatbot-clear-btn:hover {
            background: #991b1b;
        }

        .chatbot-loading {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            margin: 12px 0;
        }

        .chatbot-popup.dark .chatbot-loading {
            background: #374151;
        }

        .chatbot-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .chatbot-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin: 12px 0;
            border: 1px solid #fecaca;
        }

        .chatbot-popup.dark .chatbot-error {
            background: #7f1d1d;
            color: #fca5a5;
            border-color: #991b1b;
        }

        .chatbot-hidden {
            display: none !important;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Avatar state animations */
        @keyframes avatarPulse {
            0%, 100% { 
                background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
                transform: scale(1);
            }
            50% { 
                background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
                transform: scale(1.05);
            }
        }

        .avatar-listening .avatar-fallback {
            animation: avatarPulse 1.5s infinite;
        }

        /* Scrollbar */
        .chatbot-messages::-webkit-scrollbar {
            width: 4px;
        }

        .chatbot-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chatbot-messages::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }

        .chatbot-popup.dark .chatbot-messages::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .chatbot-popup {
                width: calc(100vw - 20px) !important;
                height: calc(100vh - 120px) !important;
                bottom: 80px !important;
                left: 10px !important;
                right: 10px !important;
                max-width: none !important;
                max-height: none !important;
            }

            .chatbot-widget.bottom-left .chatbot-popup,
            .chatbot-widget.bottom-right .chatbot-popup {
                position: fixed !important;
                bottom: 80px !important;
                left: 10px !important;
                right: 10px !important;
                width: auto !important;
            }

            .chatbot-toggle-btn {
                width: 56px !important;
                height: 56px !important;
            }
        }

        @media (max-width: 768px) {
            .chatbot-popup {
                width: 350px !important;
                height: 550px !important;
            }
        }
    `;

    // Utility functions
    const utils = {
        sanitizeText(text) {
            if (!text || typeof text !== 'string') return 'Online';
            
            let cleaned = text.replace(/[\x00-\x1F\x7F-\x9F]/g, '')
                             .replace(/[^\p{L}\p{N}\p{P}\p{Z}]/gu, '')
                             .replace(/\s+/g, ' ')
                             .trim();
            
            if (cleaned.length > 30) cleaned = cleaned.substring(0, 27) + '...';
            if (!cleaned || !/[a-zA-Z\u00C0-\u017F]/.test(cleaned)) return 'Online';
            
            return cleaned;
        },

        ready(fn) {
            if (document.readyState !== 'loading') {
                fn();
            } else {
                document.addEventListener('DOMContentLoaded', fn);
            }
        },

        createElement(tag, className, innerHTML) {
            const el = document.createElement(tag);
            if (className) el.className = className;
            if (innerHTML) el.innerHTML = innerHTML;
            return el;
        }
    };

    // Avatar management - Enhanced with better error handling
    const avatar = {
        stateGradients: {
            idle: 'linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%)',
            listening: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
            thinking: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
            speaking: 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
        },

        stateIcons: {
            idle: '🤖',
            listening: '🎤',
            thinking: '🤔',
            speaking: '💬'
        },

        loadedVideos: new Set(),

        async change(filename) {
            if (!config.avatar?.enabled) {
                console.log('🎬 Avatar disabled');
                this.showFallbacks();
                return;
            }
            
            const videoUrl = config.avatar.basePath + filename;
            console.log('🎬 Attempting to load avatar:', videoUrl);
            
            // Test if video exists first
            const videoExists = await this.testVideoExists(videoUrl);
            if (!videoExists) {
                console.warn('❌ Video file not found:', videoUrl);
                this.showFallbacks();
                return;
            }

            this.updateElement(elements.headerAvatar, videoUrl, 'header');
            this.updateElement(elements.mainAvatar, videoUrl, 'main');
        },

        async testVideoExists(url) {
            return new Promise((resolve) => {
                const video = document.createElement('video');
                video.oncanplaythrough = () => resolve(true);
                video.onerror = () => resolve(false);
                video.onabort = () => resolve(false);
                
                // Set a timeout to prevent hanging
                setTimeout(() => resolve(false), 3000);
                
                video.src = url;
                video.load();
            });
        },

        updateElement(videoElement, videoUrl, type) {
            if (!videoElement) {
                console.warn(`❌ ${type} avatar element not found`);
                return;
            }
            
            const container = videoElement.parentElement;
            
            // Show fallback immediately while loading
            this.showFallback(container, type);
            
            videoElement.onloadstart = () => {
                console.log(`🎬 Started loading ${type} avatar:`, videoUrl);
            };
            
            videoElement.oncanplaythrough = () => {
                console.log(`✅ ${type} avatar loaded successfully`);
                videoElement.style.display = 'block';
                this.hideFallback(container);
                
                // Mark as loaded
                this.loadedVideos.add(videoUrl);
                
                // Ensure video plays
                videoElement.play().catch(e => {
                    console.warn('Video autoplay prevented:', e);
                });
            };
            
            videoElement.onerror = (e) => {
                console.warn(`❌ Failed to load ${type} avatar:`, videoUrl, e);
                this.showFallback(container, type);
                videoElement.style.display = 'none';
            };
            
            // Set video properties
            videoElement.autoplay = true;
            videoElement.loop = true;
            videoElement.muted = true;
            videoElement.playsInline = true;
            
            // Load the video
            videoElement.src = videoUrl;
            videoElement.load();
        },

        showFallback(container, type) {
            if (!container) return;

            // Remove existing fallback
            const existingFallback = container.querySelector('.avatar-fallback');
            if (existingFallback) {
                existingFallback.remove();
            }

            // Set container background
            container.style.background = this.stateGradients[state.current] || this.stateGradients.idle;
            
            // Add state class for animations
            container.className = container.className.replace(/avatar-\w+/g, '') + ` avatar-${state.current}`;
            
            // Create fallback element
            const fallback = utils.createElement('div', 'avatar-fallback');
            fallback.textContent = this.stateIcons[state.current] || this.stateIcons.idle;
            
            container.appendChild(fallback);
            console.log(`🎭 Showing ${type} fallback for state: ${state.current}`);
        },

        showFallbacks() {
            if (elements.headerAvatar) {
                this.showFallback(elements.headerAvatar.parentElement, 'header');
            }
            if (elements.mainAvatar) {
                this.showFallback(elements.mainAvatar.parentElement, 'main');
            }
        },

        hideFallback(container) {
            if (!container) return;
            
            const fallback = container.querySelector('.avatar-fallback');
            if (fallback) {
                fallback.style.display = 'none';
            }
        },

        // Test all avatar videos
        async testAllVideos() {
            console.log('🧪 Testing all avatar videos...');
            const results = {};
            
            for (const [state, filename] of Object.entries(config.avatar.files)) {
                const url = config.avatar.basePath + filename;
                const exists = await this.testVideoExists(url);
                results[state] = { url, exists };
                console.log(`${exists ? '✅' : '❌'} ${state}: ${url}`);
            }
            
            return results;
        }
    };

    // Speech recognition - Fixed version
    const speech = {
        recognition: null,
        utterance: null,
        isListening: false,

        init() {
            console.log('🎤 Initializing speech recognition...');
            
            // Check browser support
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                console.warn('❌ Speech recognition not supported in this browser');
                if (elements.voiceBtn) {
                    elements.voiceBtn.disabled = true;
                    elements.voiceBtn.title = 'Speech recognition not supported in this browser';
                    elements.voiceBtn.style.opacity = '0.5';
                }
                return false;
            }

            try {
                this.recognition = new SpeechRecognition();
                
                // Configure recognition settings
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = config.language || 'id-ID';
                this.recognition.maxAlternatives = 1;
                
                // Event handlers
                this.recognition.onstart = () => {
                    console.log('🎤 Speech recognition started');
                    this.isListening = true;
                    stateManager.set('listening');
                    if (elements.voiceBtn) {
                        elements.voiceBtn.classList.add('listening');
                    }
                    if (elements.voiceText) {
                        elements.voiceText.textContent = 'Listening...';
                    }
                };
                
                this.recognition.onresult = (event) => {
                    console.log('🎤 Speech recognition result received');
                    
                    if (event.results && event.results.length > 0) {
                        const transcript = event.results[0][0].transcript;
                        const confidence = event.results[0][0].confidence;
                        
                        console.log('🎤 Transcript:', transcript, 'Confidence:', confidence);
                        
                        if (confidence > 0.3) { // Lowered threshold for better recognition
                            if (elements.input) {
                                elements.input.value = transcript;
                                inputManager.updateCharCount();
                                
                                // Auto-send if transcript is not empty
                                if (transcript.trim()) {
                                    setTimeout(() => {
                                        messageManager.send();
                                    }, 500);
                                }
                            }
                        } else {
                            ui.showError('Suara kurang jelas, silakan coba lagi. (Confidence: ' + Math.round(confidence * 100) + '%)');
                        }
                    }
                };
                
                this.recognition.onerror = (event) => {
                    console.error('🎤 Speech recognition error:', event.error);
                    this.isListening = false;
                    stateManager.set('idle');
                    this.resetVoiceButton();
                    
                    const errorMessages = {
                        'no-speech': 'Tidak ada suara yang terdeteksi. Pastikan mikrofon aktif dan berbicara dengan jelas.',
                        'audio-capture': 'Mikrofon tidak dapat diakses. Periksa pengaturan browser dan izin mikrofon.',
                        'not-allowed': 'Akses mikrofon ditolak. Klik ikon kunci di address bar dan aktifkan izin mikrofon.',
                        'network': 'Masalah koneksi internet. Periksa koneksi Anda.',
                        'service-not-allowed': 'Layanan speech recognition tidak tersedia.',
                        'bad-grammar': 'Grammar tidak valid.',
                        'language-not-supported': 'Bahasa tidak didukung.'
                    };
                    
                    if (event.error !== 'aborted') {
                        const errorMsg = errorMessages[event.error] || `Gagal mengenali suara: ${event.error}`;
                        ui.showError(errorMsg);
                    }
                };
                
                this.recognition.onend = () => {
                    console.log('🎤 Speech recognition ended');
                    this.isListening = false;
                    stateManager.set('idle');
                    this.resetVoiceButton();
                };
                
                console.log('✅ Speech recognition initialized successfully');
                return true;
                
            } catch (error) {
                console.error('❌ Failed to initialize speech recognition:', error);
                if (elements.voiceBtn) {
                    elements.voiceBtn.disabled = true;
                    elements.voiceBtn.title = 'Speech recognition initialization failed: ' + error.message;
                    elements.voiceBtn.style.opacity = '0.5';
                }
                return false;
            }
        },

        toggle() {
            console.log('🎤 Toggle speech recognition called, current state:', state.current);
            
            if (!this.recognition) {
                ui.showError('Speech recognition tidak tersedia di browser ini.');
                return;
            }
            
            // If currently listening, stop
            if (this.isListening || state.current === 'listening') {
                console.log('🎤 Stopping speech recognition...');
                try {
                    this.recognition.stop();
                } catch (error) {
                    console.error('Error stopping speech recognition:', error);
                    this.isListening = false;
                    stateManager.set('idle');
                    this.resetVoiceButton();
                }
                return;
            }
            
            // If idle, start listening
            if (state.current === 'idle' && !this.isListening) {
                console.log('🎤 Starting speech recognition...');
                try {
                    this.recognition.start();
                } catch (error) {
                    console.error('❌ Failed to start speech recognition:', error);
                    
                    let errorMsg = 'Gagal memulai pengenalan suara.';
                    if (error.name === 'InvalidStateError') {
                        errorMsg = 'Speech recognition sudah berjalan. Tunggu sebentar dan coba lagi.';
                    } else if (error.name === 'NotAllowedError') {
                        errorMsg = 'Akses mikrofon ditolak. Aktifkan izin mikrofon di pengaturan browser.';
                    }
                    
                    ui.showError(errorMsg);
                    this.resetVoiceButton();
                }
                return;
            }
            
            // If processing, show message
            ui.showError('Sedang memproses. Tunggu sebentar.');
        },

        resetVoiceButton() {
            if (elements.voiceBtn) {
                elements.voiceBtn.classList.remove('listening');
            }
            if (elements.voiceText) {
                elements.voiceText.textContent = 'Voice';
            }
        },

        // Test microphone access
        async testMicrophone() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                stream.getTracks().forEach(track => track.stop());
                console.log('✅ Microphone access granted');
                return true;
            } catch (error) {
                console.error('❌ Microphone access denied:', error);
                return false;
            }
        },

        speak(text) {
            if (!state.isSoundEnabled || !text.trim()) {
                stateManager.set('idle');
                return;
            }
            
            console.log('🔊 Speaking text:', text.substring(0, 50) + '...');
            
            speechSynthesis.cancel();
            stateManager.set('speaking');
            
            const utterance = new SpeechSynthesisUtterance(text);
            
            const voices = speechSynthesis.getVoices();
            const indonesianVoice = voices.find(voice => 
                voice.lang.includes('id') || voice.lang.includes('ID')
            ) || voices[0];
            
            if (indonesianVoice) utterance.voice = indonesianVoice;
            
            Object.assign(utterance, {
                rate: config.voiceRate,
                pitch: config.voicePitch,
                volume: 1
            });
            
            utterance.onend = () => {
                console.log('🔊 Speech ended');
                stateManager.set('idle');
            };
            
            utterance.onerror = (error) => {
                console.error('🔊 Speech error:', error);
                stateManager.set('idle');
                ui.showError('Gagal memutar suara.');
            };
            
            this.utterance = utterance;
            speechSynthesis.speak(utterance);
        }
    };

    // State management
    const stateManager = {
        set(newState) {
            console.log(`🔄 State change: ${state.current} → ${newState}`);
            const oldState = state.current;
            state.current = newState;
            
            const statusTexts = {
                idle: config.subtitle,
                listening: 'Mendengarkan...',
                thinking: 'Memproses...',
                speaking: 'Berbicara...'
            };

            ui.updateStatus(statusTexts[newState] || config.subtitle);
            
            // Change avatar with fallback
            const avatarFile = config.avatar.files[newState] || config.avatar.files.idle;
            avatar.change(avatarFile);
            
            // If avatar is disabled or fails, ensure fallbacks are updated
            if (!config.avatar?.enabled) {
                avatar.showFallbacks();
            }
        }
    };

    // Input management
    const inputManager = {
        handleChange() {
            this.updateCharCount();
        },

        updateCharCount() {
            const length = elements.input.value.length;
            elements.charCount.textContent = `${length}/500`;
            elements.charCount.classList.toggle('warning', length > 450);
        },

        handleKeyPress(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                messageManager.send();
            }
        },

        handleKeyboardShortcuts(e) {
            if (state.isWidgetOpen && e.ctrlKey && e.code === 'Space') {
                e.preventDefault();
                speech.toggle();
            }
        }
    };

    // Message management
    const messageManager = {
        async send() {
            const message = elements.input.value.trim();
            
            if (!message || state.isProcessing) return;
            
            console.log('📤 Sending message:', message);
            
            state.isProcessing = true;
            stateManager.set('thinking');
            
            try {
                ui.showLoading(true);
                
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), config.requestTimeout);
                
                const response = await fetch(config.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: state.sessionId,
                        timestamp: Date.now()
                    }),
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                const responseMessage = data.message || data.response || data.bot_response || 'Maaf, saya tidak dapat memproses permintaan Anda.';
                
                ui.displayResponse(responseMessage);
                elements.input.value = '';
                inputManager.updateCharCount();
                
                if (state.isSoundEnabled && responseMessage) {
                    speech.speak(responseMessage);
                } else {
                    stateManager.set('idle');
                }
                
            } catch (error) {
                console.error('❌ Error sending message:', error);
                
                let errorMessage = 'Maaf, terjadi kesalahan. Silakan coba lagi.';
                
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timeout. Server membutuhkan waktu terlalu lama.';
                } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet.';
                }
                
                ui.showError(errorMessage);
                ui.displayResponse('❌ ' + errorMessage);
                stateManager.set('idle');
            } finally {
                state.isProcessing = false;
                ui.hideLoading();
            }
        }
    };

    // UI management
    const ui = {
        updateStatus(message) {
            const sanitizedMessage = utils.sanitizeText(message);
            if (elements.statusText) {
                elements.statusText.textContent = sanitizedMessage;
            }
        },

        displayResponse(text) {
            elements.responseText.textContent = text;
            elements.response.classList.remove('chatbot-hidden');
            elements.messages.scrollTop = elements.messages.scrollHeight;
        },

        showLoading(show) {
            if (show) {
                const loading = utils.createElement('div', 'chatbot-loading', `
                    <div class="chatbot-spinner"></div>
                    <span>AI sedang menyiapkan jawaban...</span>
                `);
                loading.id = 'chatbot-loading';
                elements.messages.appendChild(loading);
                elements.messages.scrollTop = elements.messages.scrollHeight;
            } else {
                const loading = document.getElementById('chatbot-loading');
                if (loading) loading.remove();
            }
        },

        hideLoading() {
            this.showLoading(false);
        },

        showError(message) {
            console.error('❌', message);
            
            const error = utils.createElement('div', 'chatbot-error', message);
            elements.messages.appendChild(error);
            elements.messages.scrollTop = elements.messages.scrollHeight;
            
            setTimeout(() => {
                if (error.parentNode) error.remove();
            }, 5000);
        },

        toggleTheme() {
            state.isDarkMode = !state.isDarkMode;
            localStorage.setItem('chatbot-darkMode', state.isDarkMode);
            
            elements.popup.classList.toggle('dark', state.isDarkMode);
            
            if (state.isDarkMode) {
                elements.darkIcon.classList.add('chatbot-hidden');
                elements.lightIcon.classList.remove('chatbot-hidden');
            } else {
                elements.darkIcon.classList.remove('chatbot-hidden');
                elements.lightIcon.classList.add('chatbot-hidden');
            }
            
            console.log('🌙 Theme updated:', state.isDarkMode ? 'Dark' : 'Light');
        },

        toggleSound() {
            state.isSoundEnabled = !state.isSoundEnabled;
            localStorage.setItem('chatbot-soundEnabled', state.isSoundEnabled);
            
            if (state.isSoundEnabled) {
                elements.soundOn.classList.remove('chatbot-hidden');
                elements.soundOff.classList.add('chatbot-hidden');
                elements.soundToggle.title = 'Disable Sound';
            } else {
                elements.soundOn.classList.add('chatbot-hidden');
                elements.soundOff.classList.remove('chatbot-hidden');
                elements.soundToggle.title = 'Enable Sound';
                
                if (speech.utterance) {
                    speechSynthesis.cancel();
                    stateManager.set('idle');
                }
            }
            
            console.log('🔊 Sound toggled:', state.isSoundEnabled ? 'ON' : 'OFF');
        },

        toggleWidget() {
            state.isWidgetOpen = !state.isWidgetOpen;
            
            if (state.isWidgetOpen) {
                elements.popup.classList.add('open');
                elements.chatIcon.classList.add('chatbot-hidden');
                elements.closeIcon.classList.remove('chatbot-hidden');
                elements.input.focus();
                this.ensureCorrectPositioning();
            } else {
                elements.popup.classList.remove('open');
                elements.chatIcon.classList.remove('chatbot-hidden');
                elements.closeIcon.classList.add('chatbot-hidden');
            }
            
            console.log('🎮 Widget toggled:', state.isWidgetOpen ? 'Open' : 'Closed');
        },

        clearChat() {
            elements.input.value = '';
            elements.response.classList.add('chatbot-hidden');
            elements.responseText.textContent = '';
            inputManager.updateCharCount();
            
            const dynamicMessages = elements.messages.querySelectorAll('.chatbot-loading, .chatbot-error');
            dynamicMessages.forEach(msg => msg.remove());
            
            if (speech.utterance) speechSynthesis.cancel();
            stateManager.set('idle');
            console.log('🗑️ Chat cleared');
        },

        ensureCorrectPositioning() {
            const widget = document.getElementById('chatbot-widget-container');
            if (!widget) return;

            widget.style.position = 'fixed';
            widget.style.zIndex = '999999';
            widget.style.transform = 'none';
            widget.style.margin = '0';
            widget.style.padding = '0';
            
            if (config.position === 'bottom-right') {
                Object.assign(widget.style, {
                    bottom: '20px',
                    right: '20px',
                    left: 'auto',
                    top: 'auto'
                });
            } else {
                Object.assign(widget.style, {
                    bottom: '20px',
                    left: '20px',
                    right: 'auto',
                    top: 'auto'
                });
            }
        }
    };

    // Widget creation and initialization
    const widget = {
        addStyles() {
            console.log('📝 Adding CSS styles...');
            
            const styleSheet = utils.createElement('style');
            styleSheet.id = 'chatbot-widget-styles';
            styleSheet.textContent = widgetStyles;
            document.head.appendChild(styleSheet);

            const overrideStyles = utils.createElement('style');
            overrideStyles.id = 'chatbot-widget-overrides';
            overrideStyles.textContent = `
                #chatbot-widget-container {
                    position: fixed !important;
                    z-index: 999999 !important;
                    bottom: 20px !important;
                    ${config.position === 'bottom-right' ? 'right: 20px !important; left: auto !important;' : 'left: 20px !important; right: auto !important;'}
                    top: auto !important;
                    transform: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: auto !important;
                    height: auto !important;
                    max-width: none !important;
                    max-height: none !important;
                    overflow: visible !important;
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
            `;
            document.head.appendChild(overrideStyles);
            
            console.log('✅ Styles added');
        },

        createHTML() {
            console.log('🏗️ Creating widget HTML structure...');
            
            const widgetContainer = utils.createElement('div', `chatbot-widget ${config.position}`);
            widgetContainer.id = 'chatbot-widget-container';
            
            // Force positioning with inline styles
            Object.assign(widgetContainer.style, {
                position: 'fixed',
                zIndex: '999999',
                bottom: '20px',
                [config.position === 'bottom-right' ? 'right' : 'left']: '20px',
                [config.position === 'bottom-right' ? 'left' : 'right']: 'auto',
                top: 'auto'
            });
        
            widgetContainer.innerHTML = `
                <button class="chatbot-toggle-btn" id="chatbot-toggle">
                    <svg id="chatbot-chat-icon" width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                    </svg>
                    <svg id="chatbot-close-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20" class="chatbot-hidden">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
    
                <div class="chatbot-popup ${state.isDarkMode ? 'dark' : ''}" id="chatbot-popup">
                    <div class="chatbot-header">
                        <div class="chatbot-header-content">
                            <div class="chatbot-avatar-container">
                                <div class="chatbot-avatar">
                                    <video id="chatbot-header-avatar" autoplay loop muted playsinline style="display: none;"></video>
                                </div>
                                <div class="chatbot-info">
                                    <h3>${config.title}</h3>
                                    <p id="chatbot-status-text">${config.subtitle}</p>
                                </div>
                            </div>
                            
                            <div class="chatbot-controls">
                                <button class="chatbot-control-btn" id="chatbot-sound-toggle" title="Toggle Sound">
                                    <svg id="chatbot-sound-on" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.764L4.576 14H2a1 1 0 01-1-1V7a1 1 0 011-1h2.576l3.807-2.764a1 1 0 011.617.764zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.983 5.983 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.984 3.984 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                                    </svg>
                                    <svg id="chatbot-sound-off" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" class="chatbot-hidden">
                                        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.764L4.576 14H2a1 1 0 01-1-1V7a1 1 0 011-1h2.576l3.807-2.764a1 1 0 011.617.764zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <button class="chatbot-control-btn" id="chatbot-theme-toggle" title="Toggle Theme">
                                    <svg id="chatbot-dark-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                    </svg>
                                    <svg id="chatbot-light-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" class="chatbot-hidden">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <button class="chatbot-control-btn" id="chatbot-close-btn" title="Close">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
    
                    <div class="chatbot-body">
                        <div class="chatbot-avatar-display">
                            <div class="chatbot-main-avatar">
                                <video id="chatbot-main-avatar" autoplay loop muted playsinline style="display: none;"></video>
                            </div>
                        </div>

                        <div class="chatbot-messages" id="chatbot-messages">
                            <div class="chatbot-message chatbot-hidden" id="chatbot-response">
                                <div id="chatbot-response-text"></div>
                            </div>
                        </div>
    
                        <div class="chatbot-input-area">
                            <div class="chatbot-input-container">
                                <input type="text" id="chatbot-input" class="chatbot-input" placeholder="Ketik pesan Anda di sini..." maxlength="500">
                                <div class="chatbot-char-count" id="chatbot-char-count">0/500</div>
                            </div>
                            
                            <div class="chatbot-actions">
                                <button class="chatbot-action-btn chatbot-voice-btn" id="chatbot-voice-btn" title="Voice Input">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                                    </svg>
                                    <span id="chatbot-voice-text">Voice</span>
                                </button>
                                
                                <button class="chatbot-action-btn chatbot-send-btn" id="chatbot-send-btn" title="Send Message">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="transform: rotate(90deg);">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                    </svg>
                                    <span>Kirim</span>
                                </button>
                            </div>
                            
                            <div class="chatbot-quick-actions">
                                <button class="chatbot-quick-btn" data-message="Halo, apa kabar?">👋 Sapa</button>
                                <button class="chatbot-quick-btn" data-message="Bisakah Anda membantu saya?">🤝 Bantuan</button>
                                <button class="chatbot-quick-btn" data-message="Apa fitur yang tersedia?">⚡ Fitur</button>
                                <button class="chatbot-quick-btn chatbot-clear-btn" id="chatbot-clear-btn">🗑️ Clear</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    
            document.body.appendChild(widgetContainer);
            
            // Force positioning after DOM insertion
            setTimeout(() => ui.ensureCorrectPositioning(), 100);
            
            console.log('✅ Widget HTML created and appended');
        },

        cacheElements() {
            elements = {
                toggle: document.getElementById('chatbot-toggle'),
                popup: document.getElementById('chatbot-popup'),
                chatIcon: document.getElementById('chatbot-chat-icon'),
                closeIcon: document.getElementById('chatbot-close-icon'),
                statusText: document.getElementById('chatbot-status-text'),
                headerAvatar: document.getElementById('chatbot-header-avatar'),
                mainAvatar: document.getElementById('chatbot-main-avatar'),
                messages: document.getElementById('chatbot-messages'),
                response: document.getElementById('chatbot-response'),
                responseText: document.getElementById('chatbot-response-text'),
                input: document.getElementById('chatbot-input'),
                charCount: document.getElementById('chatbot-char-count'),
                voiceBtn: document.getElementById('chatbot-voice-btn'),
                voiceText: document.getElementById('chatbot-voice-text'),
                sendBtn: document.getElementById('chatbot-send-btn'),
                clearBtn: document.getElementById('chatbot-clear-btn'),
                soundToggle: document.getElementById('chatbot-sound-toggle'),
                soundOn: document.getElementById('chatbot-sound-on'),
                soundOff: document.getElementById('chatbot-sound-off'),
                themeToggle: document.getElementById('chatbot-theme-toggle'),
                darkIcon: document.getElementById('chatbot-dark-icon'),
                lightIcon: document.getElementById('chatbot-light-icon'),
                closeBtn: document.getElementById('chatbot-close-btn')
            };
        },

        setupEventListeners() {
            // Toggle widget
            elements.toggle.addEventListener('click', ui.toggleWidget);
            elements.closeBtn.addEventListener('click', ui.toggleWidget);

            // Input handling
            elements.input.addEventListener('input', inputManager.handleChange);
            elements.input.addEventListener('keypress', inputManager.handleKeyPress);

            // Buttons
            elements.sendBtn.addEventListener('click', messageManager.send);
            elements.voiceBtn.addEventListener('click', () => {
                console.log('🎤 Voice button clicked');
                speech.toggle();
            });
            elements.clearBtn.addEventListener('click', ui.clearChat);
            elements.soundToggle.addEventListener('click', ui.toggleSound);
            elements.themeToggle.addEventListener('click', ui.toggleTheme);

            // Quick actions
            document.querySelectorAll('.chatbot-quick-btn:not(.chatbot-clear-btn)').forEach(btn => {
                btn.addEventListener('click', function() {
                    const message = this.getAttribute('data-message');
                    elements.input.value = message;
                    messageManager.send();
                });
            });

            // Keyboard shortcuts - Improved
            document.addEventListener('keydown', (e) => {
                inputManager.handleKeyboardShortcuts(e);
                
                // Additional shortcut for voice (Ctrl + Shift + V)
                if (e.ctrlKey && e.shiftKey && e.code === 'KeyV' && state.isWidgetOpen) {
                    e.preventDefault();
                    console.log('🎤 Voice shortcut triggered');
                    speech.toggle();
                }
            });
            
            console.log('✅ Event listeners setup completed');
        },

        setupPositionMonitoring() {
            setInterval(() => {
                const widget = document.getElementById('chatbot-widget-container');
                if (widget) {
                    const computed = window.getComputedStyle(widget);
                    if (computed.position !== 'fixed' || computed.zIndex !== '999999') {
                        ui.ensureCorrectPositioning();
                    }
                }
            }, 2000);

            window.addEventListener('scroll', ui.ensureCorrectPositioning);
            window.addEventListener('resize', ui.ensureCorrectPositioning);
        },

        async init() {
            console.log('🤖 Initializing AI Chatbot Widget...');
            
            try {
                this.addStyles();
                this.createHTML();
                this.cacheElements();
                this.setupEventListeners();
                
                // Initialize speech recognition
                console.log('🎤 Initializing speech recognition...');
                const speechInitialized = speech.init();
                
                if (speechInitialized) {
                    console.log('✅ Speech recognition ready');
                    // Test microphone access
                    const micAccess = await speech.testMicrophone();
                    if (!micAccess) {
                        console.warn('⚠️ Microphone access not available');
                        if (elements.voiceBtn) {
                            elements.voiceBtn.title = 'Microphone access required. Click to grant permission.';
                        }
                    }
                } else {
                    console.warn('⚠️ Speech recognition not available');
                }
                
                ui.toggleTheme(); // Apply saved theme
                ui.toggleSound(); // Apply saved sound setting
                this.setupPositionMonitoring();
                
                // Test avatar videos first
                console.log('🎬 Testing avatar videos...');
                const videoResults = await avatar.testAllVideos();
                const workingVideos = Object.values(videoResults).filter(r => r.exists).length;
                
                if (workingVideos === 0) {
                    console.warn('⚠️ No avatar videos found, using fallback icons');
                    config.avatar.enabled = false;
                }
                
                setTimeout(() => {
                    if (config.avatar.enabled) {
                        avatar.change(config.avatar.files.idle);
                    } else {
                        avatar.showFallbacks();
                    }
                    console.log('✅ AI Chatbot Widget initialized successfully');
                    
                    // Log final status
                    console.log('🔍 Final Status:');
                    console.log('- Speech Recognition:', !!speech.recognition);
                    console.log('- Avatar Enabled:', config.avatar.enabled);
                    console.log('- Widget Ready:', true);
                }, 500);
                
            } catch (error) {
                console.error('❌ Error initializing chatbot widget:', error);
            }
        }
    };

    // Load voices for TTS
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = function() {
            const voices = speechSynthesis.getVoices();
            console.log('🔊 Available voices:', voices.length);
            
            const indonesianVoices = voices.filter(voice => 
                voice.lang.includes('id') || voice.lang.includes('ID')
            );
            if (indonesianVoices.length > 0) {
                console.log('🔊 Indonesian voices found:', indonesianVoices.map(v => v.name));
            }
        };
    }

    // Public API
    window.ChatbotWidget = {
        init: widget.init,
        toggle: ui.toggleWidget,
        sendMessage: function(message) {
            if (elements.input) {
                elements.input.value = message;
                messageManager.send();
            }
        },
        setState: stateManager.set,
        toggleTheme: ui.toggleTheme,
        toggleSound: ui.toggleSound,
        changeAvatar: avatar.change,
        testVideos: function() {
            console.log('🎬 Testing all avatar videos...');
            avatar.change(config.avatar.files.idle);
            setTimeout(() => avatar.change(config.avatar.files.listening), 2000);
            setTimeout(() => avatar.change(config.avatar.files.thinking), 4000);
            setTimeout(() => avatar.change(config.avatar.files.speaking), 6000);
            setTimeout(() => avatar.change(config.avatar.files.idle), 8000);
        },
        testAvatarVideos: avatar.testAllVideos,
        testSpeechRecognition: function() {
            console.log('🎤 Testing speech recognition...');
            console.log('- Browser support:', !!(window.SpeechRecognition || window.webkitSpeechRecognition));
            console.log('- Speech object:', !!speech.recognition);
            console.log('- Current state:', state.current);
            console.log('- Is listening:', speech.isListening);
            
            if (speech.recognition) {
                console.log('✅ Speech recognition is available');
                console.log('- Language:', speech.recognition.lang);
                console.log('- Continuous:', speech.recognition.continuous);
                console.log('- Interim results:', speech.recognition.interimResults);
                return true;
            } else {
                console.log('❌ Speech recognition is not available');
                return false;
            }
        },
        testMicrophone: speech.testMicrophone,
        startListening: function() {
            console.log('🎤 Manual start listening...');
            speech.toggle();
        },
        debug: function() {
            console.log('🔍 Widget Debug Info:');
            console.log('- State:', state);
            console.log('- Config:', config);
            console.log('- Elements:', Object.keys(elements).filter(key => !!elements[key]));
            console.log('- Avatar videos loaded:', avatar.loadedVideos);
            console.log('- Speech recognition:', !!speech.recognition);
            console.log('- Microphone support:', !!navigator.mediaDevices?.getUserMedia);
        },
        config: config,
        elements: elements
    };

    // Auto-initialize
    utils.ready(function() {
        console.log('🚀 Chatbot Widget DOM ready, starting initialization...');
        try {
            widget.init();
            console.log('🎉 Chatbot Widget ready and running!');
        } catch (error) {
            console.error('💥 Failed to initialize chatbot widget:', error);
        }
    });

    console.log('🤖 Chatbot Widget script loaded');

})();