<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI Chatbot dengan teknologi RAG untuk menjawab pertanyaan tentang website">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chatbot - Asisten Virtual Cerdas</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- SEO Meta Tags -->
    <meta property="og:title" content="AI Chatbot - Asisten Virtual Cerdas">
    <meta property="og:description" content="Chatbot AI dengan teknologi RAG untuk menjawab pertanyaan">
    <meta property="og:type" content="website">
    
    <!-- Configure Tailwind -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-in': 'bounceIn 0.6s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        /* Pastikan font rendering yang benar */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Pastikan text status tidak corruption */
        #popup-status-text {
            font-family: 'Inter', sans-serif !important;
            font-weight: 400;
            font-size: 0.75rem;
            line-height: 1rem;
            letter-spacing: 0.025em;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .animate-slide-up {
            animation: slideUp 0.3s ease-out;
        }
        
        .animate-bounce-in {
            animation: bounceIn 0.6s ease-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }
        
        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: white; }
        }
        
        @keyframes loadingDot {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1.2); opacity: 1; }
        }
        
        .loading-dot {
            animation: loadingDot 1.4s infinite ease-in-out both;
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        .loading-dot:nth-child(3) { animation-delay: 0s; }
        
        .typing-animation {
            white-space: nowrap;
            overflow: hidden;
            border-right: 2px solid white;
            width: 0;
            animation: typing 6s steps(60, end) forwards, blink-caret 1.5s step-end infinite;
        }
        
        .status-online {
            background-color: #10b981; /* green-500 */
        }
        
        .status-busy {
            background-color: #f59e0b; /* amber-500 */
        }
        
        .status-offline {
            background-color: #ef4444; /* red-500 */
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-bg-dark {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .loading-dot {
            animation: loadingDot 1.4s infinite ease-in-out both;
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes loadingDot {
            0%, 80%, 100% { 
                transform: scale(0);
            } 40% { 
                transform: scale(1);
            }
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
        }
        
        .focus-ring:focus {
            outline: none;
            ring: 2px;
            ring-color: rgb(59 130 246 / 0.5);
        }
        
        .status-online {
            animation: statusPulse 2s infinite;
        }
        
        @keyframes statusPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Hide scrollbar untuk area chat */
        .chat-scroll::-webkit-scrollbar {
            display: none;
        }
        
        .chat-scroll {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer 10+ */
        }
    </style>
</head>

<body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <!-- Background gradient -->
    <div class="fixed inset-0 bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900"></div>
    
    <!-- Animated background particles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-blue-400 opacity-30 rounded-full animate-float"></div>
        <div class="absolute top-3/4 right-1/3 w-1 h-1 bg-purple-400 opacity-40 rounded-full animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-3/4 w-3 h-3 bg-blue-300 opacity-20 rounded-full animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/3 right-1/4 w-2 h-2 bg-purple-300 opacity-25 rounded-full animate-float" style="animation-delay: 3s;"></div>
    </div>
    
    <!-- Main Content -->
    <div class="relative z-10 min-h-screen">
        <!-- Header -->
        <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg border-b border-white/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">AI Assistant</h1>
                    </div>
                    <nav class="hidden md:flex space-x-8">
                        <a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Features</a>
                        <a href="#about" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">About</a>
                        <a href="#contact" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Contact</a>
                    </nav>
                    <button id="main-theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center">
                <div class="mb-12">
                    <h1 class="text-5xl md:text-7xl font-bold text-gray-900 dark:text-white mb-6">
                        Meet Your
                        <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            AI Assistant
                        </span>
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-8">
                        Experience the future of conversation with our advanced AI chatbot. Get instant answers, creative assistance, and intelligent support powered by cutting-edge technology.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button onclick="toggleChatbot()" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200">
                            Start Chatting
                        </button>
                        <button class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-8 py-4 rounded-xl font-semibold text-lg border border-gray-200 dark:border-gray-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200">
                            Learn More
                        </button>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-3 gap-8 mt-20">
                    <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-md p-8 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Instant Answers</h3>
                        <p class="text-gray-600 dark:text-gray-300">Get immediate responses to your questions with our lightning-fast AI processing.</p>
                    </div>

                    <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-md p-8 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Voice Support</h3>
                        <p class="text-gray-600 dark:text-gray-300">Speak naturally and hear responses with our advanced voice recognition technology.</p>
                    </div>

                    <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-md p-8 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Smart & Secure</h3>
                        <p class="text-gray-600 dark:text-gray-300">Enjoy intelligent conversations with enterprise-grade security and privacy protection.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white/40 dark:bg-gray-800/40 backdrop-blur-md">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">99.9%</div>
                        <div class="text-gray-600 dark:text-gray-300">Uptime</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">< 100ms</div>
                        <div class="text-gray-600 dark:text-gray-300">Response Time</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">50+</div>
                        <div class="text-gray-600 dark:text-gray-300">Languages</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-pink-600 dark:text-pink-400 mb-2">24/7</div>
                        <div class="text-gray-600 dark:text-gray-300">Available</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Chatbot Popup Widget -->
    <div id="chatbot-popup" class="fixed bottom-6 right-6 z-50 hidden">
        <!-- Chatbot Container -->
        <div class="w-96 h-[800px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-bounce-in chatbot-popup-container">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-4 text-white relative overflow-hidden">
                <!-- Background pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-16 h-16 border border-white rounded-full"></div>
                    <div class="absolute bottom-0 right-0 w-12 h-12 border border-white rounded-full"></div>
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- Avatar Container -->
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/30">
                                    <video id="popup-avatar-video" 
                                           class="w-full h-full object-cover" 
                                           autoplay 
                                           loop 
                                           muted 
                                           playsinline
                                           onloadstart="console.log('Loading popup avatar video')"
                                           oncanplay="console.log('Popup avatar video ready')"
                                           onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <source src="{{ asset('videos/avatar/idle.mp4') }}" type="video/mp4">
                                    </video>
                                    <!-- Fallback avatar -->
                                    <div class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center" style="display: none;">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Status indicator -->
                                <div id="popup-status-indicator" class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                            </div>
                            
                            <div>
                                <h3 class="font-semibold text-sm">AI Assistant</h3>
                                <p id="popup-status-text" class="text-xs text-white/80">Online & Ready</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <!-- Sound toggle button -->
                            <button id="sound-toggle-btn" onclick="toggleSound()" class="p-1 rounded-lg bg-white/10 hover:bg-white/20 transition-all duration-200" title="Toggle Sound">
                                <svg id="sound-on-icon" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.764L4.576 14H2a1 1 0 01-1-1V7a1 1 0 011-1h2.576l3.807-2.764a1 1 0 011.617.764zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.983 5.983 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.984 3.984 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                                </svg>
                                <svg id="sound-off-icon" class="w-4 h-4 hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.764L4.576 14H2a1 1 0 01-1-1V7a1 1 0 011-1h2.576l3.807-2.764a1 1 0 011.617.764zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <!-- Dark mode toggle button -->
                            <button id="chatbot-theme-toggle" onclick="toggleTheme()" class="p-1 rounded-lg bg-white/10 hover:bg-white/20 transition-all duration-200" title="Toggle Dark Mode">
                                <svg id="dark-mode-icon" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                </svg>
                                <svg id="light-mode-icon" class="w-4 h-4 hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <!-- Close button -->
                            <button onclick="toggleChatbot()" class="p-1 rounded-lg bg-white/10 hover:bg-white/20 transition-all duration-200">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chat Container dengan Flex Layout - Avatar Area + Messages + Input -->
            <div class="flex flex-col h-[720px]">
                <!-- Avatar Display Area - Fixed Height -->
                <div class="flex-shrink-0 flex flex-col items-center space-y-3 p-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-700 dark:to-gray-800">
                    <div class="relative">
                        <!-- Avatar bulat di tengah - diperbesar 3 kali lipat -->
                        <div class="w-96 h-96 rounded-full overflow-hidden shadow-2xl border-4 border-white/50">
                            <video id="main-avatar-video" 
                                   class="w-full h-full object-cover" 
                                   autoplay 
                                   loop 
                                   muted 
                                   playsinline
                                   onloadstart="console.log('Loading main avatar video')"
                                   oncanplay="console.log('Main avatar video ready')"
                                   onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <source src="{{ asset('videos/avatar/idle.mp4') }}" type="video/mp4">
                            </video>
                            <!-- Fallback avatar -->
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center" style="display: none;">
                                <svg class="w-48 h-48 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <!-- Pulse animation for active states -->
                        <div id="avatar-pulse" class="absolute inset-0 rounded-full bg-blue-400 opacity-0 animate-ping"></div>
                    </div>
                    
                    <!-- Current Action Display - minimal -->
                    <div class="min-h-4 flex items-center justify-center">
                        <div id="action-display" class="text-center">
                            <!-- Listening state -->
                            <div id="listening-state" class="hidden">
                                <div class="flex justify-center space-x-1">
                                    <div class="w-1 h-4 bg-blue-500 rounded-full animate-pulse"></div>
                                    <div class="w-1 h-3 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.1s;"></div>
                                    <div class="w-1 h-5 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                                    <div class="w-1 h-3 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.3s;"></div>
                                    <div class="w-1 h-4 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                                </div>
                            </div>
                            
                            <!-- Thinking state -->
                            <div id="thinking-state" class="hidden">
                                <div class="flex justify-center space-x-1">
                                    <div class="w-1 h-1 bg-purple-500 rounded-full loading-dot"></div>
                                    <div class="w-1 h-1 bg-purple-500 rounded-full loading-dot"></div>
                                    <div class="w-1 h-1 bg-purple-500 rounded-full loading-dot"></div>
                                </div>
                            </div>
                            
                            <!-- Speaking state -->
                            <div id="speaking-state" class="hidden">
                                <div class="text-xs text-green-600 dark:text-green-400 animate-pulse">🎵 Berbicara...</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Message Display Area - Scrollable without scrollbar -->
                <div class="flex-1 p-4 overflow-y-auto chatbot-scrollable bg-white dark:bg-gray-800">
                    <div id="last-response" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 min-h-12 hidden">
                        <div class="text-sm text-gray-700 dark:text-gray-300" id="response-text"></div>
                    </div>
                </div>
                
                <!-- Input Section - Fixed at Bottom -->
                <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-600 p-4 bg-white dark:bg-gray-800">
                    <div class="space-y-3">
                        <!-- Text input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                id="message-input"
                                placeholder="Ketik pesan Anda di sini..."
                                class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-sm"
                                maxlength="500"
                            >
                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 text-xs text-gray-400">
                                <span id="char-count">0</span>/500
                            </div>
                        </div>
                        
                        <!-- Action buttons -->
                        <div class="flex space-x-2">
                            <!-- Voice input button -->
                            <button 
                                id="voice-btn"
                                class="flex-1 flex items-center justify-center space-x-2 py-2 px-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 text-sm"
                                title="Voice Input (Ctrl+Space)"
                            >
                                <svg id="mic-icon" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                                </svg>
                                <span id="voice-text">Voice</span>
                            </button>
                            
                            <!-- Send button dengan icon dirotate -->
                            <button 
                                id="send-btn"
                                class="flex-1 flex items-center justify-center space-x-2 py-2 px-3 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-lg transition-all duration-200 text-sm"
                                title="Send Message (Enter)"
                            >
                                <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                </svg>
                                <span>Kirim</span>
                            </button>
                        </div>
                        
                        <!-- Quick actions -->
                        <div class="flex flex-wrap gap-1">
                            <button class="quick-action px-2 py-1 text-xs bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors" data-message="Halo, apa kabar?">
                                👋 Sapa
                            </button>
                            <button class="quick-action px-2 py-1 text-xs bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors" data-message="Bisakah Anda membantu saya?">
                                🤝 Bantuan
                            </button>
                            <button class="quick-action px-2 py-1 text-xs bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors" data-message="Apa fitur yang tersedia?">
                                ⚡ Fitur
                            </button>
                            <button id="clear-btn" class="px-2 py-1 text-xs bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-300 rounded-full hover:bg-red-200 dark:hover:bg-red-700 transition-colors">
                                🗑️ Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Chat Button -->
    <button id="chat-toggle-btn" onclick="toggleChatbot()" class="fixed bottom-6 right-6 z-40 w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 flex items-center justify-center group">
        <svg id="chat-icon" class="w-8 h-8 transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
        </svg>
        <svg id="close-icon" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Loading overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="text-gray-700 dark:text-gray-300">Memproses...</span>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Configuration
        const CONFIG = {
            apiEndpoint: '/api/chatbot/message',
            maxRetries: 3,
            retryDelay: 1000,
            speechLang: 'id-ID',
            voiceRate: 0.9,
            voicePitch: 1,
            animationDuration: 300,
            requestTimeout: 30000 // 30 seconds
        };

        // State management
        let currentState = 'idle'; // idle, listening, thinking, speaking
        let isProcessing = false;
        let recognition = null;
        let currentUtterance = null;
        let isDarkMode = localStorage.getItem('darkMode') === 'true';
        let isSoundEnabled = localStorage.getItem('soundEnabled') !== 'false';
        let sessionId = `session_${Date.now()}`;

        // DOM elements
        const elements = {
            messageInput: document.getElementById('message-input'),
            sendBtn: document.getElementById('send-btn'),
            voiceBtn: document.getElementById('voice-btn'),
            clearBtn: document.getElementById('clear-btn'),
            charCount: document.getElementById('char-count'),
            statusText: document.getElementById('popup-status-text'),
            statusIndicator: document.getElementById('popup-status-indicator'),
            avatarVideo: document.getElementById('popup-avatar-video'),
            mainAvatarVideo: document.getElementById('main-avatar-video'),
            avatarPulse: document.getElementById('avatar-pulse'),
            actionDisplay: document.getElementById('action-display'),
            lastResponse: document.getElementById('last-response'),
            responseText: document.getElementById('response-text'),
            responseTime: document.getElementById('response-time'),
            loadingOverlay: document.getElementById('loading-overlay')
        };

        // Initialize dengan monitoring interval
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
            setupEventListeners();
            setupSpeechRecognition();
            updateTheme();
            updateSoundState();
            
            // Set monitoring interval untuk text corruption
            setInterval(monitorTextCorruption, 5000); // Check setiap 5 detik
        });

        // App initialization
        function initializeApp() {
            console.log('🤖 Initializing AI Chatbot...');
            
            // Set CSRF token
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                window.axios = window.axios || {};
                window.axios.defaults = window.axios.defaults || {};
                window.axios.defaults.headers = window.axios.defaults.headers || {};
                window.axios.defaults.headers.common = window.axios.defaults.headers.common || {};
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
            }
            
            // Reset status dengan text yang bersih
            resetChatbotStatus();
            
            console.log('✅ AI Chatbot initialized successfully');
        }

        // Reset status chatbot ke kondisi normal
        function resetChatbotStatus() {
            // Set status text dengan aman
            const defaultStatus = 'Online & Ready';
            if (elements.statusText) {
                elements.statusText.textContent = defaultStatus;
            }
            
            // Set status indicator
            if (elements.statusIndicator) {
                elements.statusIndicator.className = 'absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 border-2 border-white rounded-full status-online';
            }
            
            // Pastikan avatar dalam kondisi idle
            changeAvatarVideo('idle.mp4');
        }

        // Event listeners setup
        function setupEventListeners() {
            // Message input
            elements.messageInput.addEventListener('input', handleInputChange);
            elements.messageInput.addEventListener('keypress', handleKeyPress);
            
            // Buttons
            elements.sendBtn.addEventListener('click', sendMessage);
            elements.voiceBtn.addEventListener('click', toggleVoiceInput);
            elements.clearBtn.addEventListener('click', clearChat);
            
            // Quick actions
            document.querySelectorAll('.quick-action').forEach(btn => {
                btn.addEventListener('click', function() {
                    const message = this.getAttribute('data-message');
                    elements.messageInput.value = message;
                    sendMessage();
                });
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', handleKeyboardShortcuts);
        }

        // Speech recognition setup
        function setupSpeechRecognition() {
            // Check for speech recognition support
            const hasSpeechRecognition = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
            
            if (!hasSpeechRecognition) {
                console.warn('Speech recognition not supported in this browser');
                elements.voiceBtn.disabled = true;
                elements.voiceBtn.title = 'Speech recognition not supported in this browser. Try Chrome, Edge, or Safari.';
                elements.voiceBtn.innerHTML = `
                    <svg class="w-4 h-4 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.366zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Not Supported</span>
                `;
                return;
            }
            
            try {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                recognition = new SpeechRecognition();
                
                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.lang = CONFIG.speechLang;
                recognition.maxAlternatives = 1;
                
                recognition.onstart = function() {
                    console.log('🎤 Speech recognition started');
                    setState('listening');
                    elements.voiceBtn.innerHTML = `
                        <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                        </svg>
                        <span>Listening...</span>
                    `;
                };
                
                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript;
                    const confidence = event.results[0][0].confidence;
                    console.log('🎤 Speech recognized:', transcript, 'confidence:', confidence);
                    
                    if (confidence > 0.5) {
                        elements.messageInput.value = transcript;
                        updateCharCount();
                        
                        // Auto-send if transcript is not empty
                        if (transcript.trim()) {
                            setTimeout(() => sendMessage(), 500);
                        }
                    } else {
                        showError('Suara kurang jelas, silakan coba lagi.');
                    }
                };
                
                recognition.onerror = function(event) {
                    console.error('🎤 Speech recognition error:', event.error);
                        setState('idle');
                    
                    let errorMessage = 'Gagal mengenali suara.';
                    switch(event.error) {
                        case 'no-speech':
                            errorMessage = 'Tidak ada suara yang terdeteksi. Pastikan mikrofon aktif.';
                            break;
                        case 'audio-capture':
                            errorMessage = 'Mikrofon tidak dapat diakses. Periksa pengaturan browser.';
                            break;
                        case 'not-allowed':
                            errorMessage = 'Akses mikrofon ditolak. Aktifkan izin mikrofon di browser.';
                            break;
                        case 'network':
                            errorMessage = 'Masalah koneksi internet. Periksa koneksi Anda.';
                            break;
                        case 'aborted':
                            // Don't show error for manual abort
                            return;
                        default:
                            errorMessage = `Error: ${event.error}. Coba gunakan browser Chrome atau Edge.`;
                    }
                    showError(errorMessage);
                    setState('idle');
                    resetVoiceButton();
                };
                
                recognition.onend = function() {
                    console.log('🎤 Speech recognition ended');
                    setState('idle');
                    resetVoiceButton();
                };
                
                // Test speech recognition capability
                console.log('✅ Speech recognition initialized successfully');
                
            } catch (error) {
                console.error('Failed to initialize speech recognition:', error);
                elements.voiceBtn.disabled = true;
                elements.voiceBtn.title = 'Speech recognition initialization failed. Try refreshing the page.';
                showError('Gagal menginisialisasi speech recognition. Coba refresh halaman.');
            }
        }
        
        // Reset voice button to normal state
        function resetVoiceButton() {
            elements.voiceBtn.innerHTML = `
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                </svg>
                <span>Voice</span>
            `;
        }

        // State management with improved avatar sync
        function setState(newState) {
            console.log(`🔄 State change: ${currentState} → ${newState}`);
            currentState = newState;
            
            // Hide all state displays
            document.querySelectorAll('[id$="-state"]').forEach(el => el.classList.add('hidden'));
            
            // Update avatar and display with smooth transitions
            switch (newState) {
                case 'idle':
                    updateStatus('ready', 'Online & Ready');
                    changeAvatarVideo('idle.mp4');
                    elements.avatarPulse.style.opacity = '0';
                    elements.avatarPulse.classList.remove('animate-ping');
                    break;
                    
                case 'listening':
                    document.getElementById('listening-state').classList.remove('hidden');
                    updateStatus('listening', 'Mendengarkan...');
                    changeAvatarVideo('listening.mp4');
                    elements.avatarPulse.style.opacity = '0.3';
                    elements.avatarPulse.classList.add('animate-ping');
                    break;
                    
                case 'thinking':
                    document.getElementById('thinking-state').classList.remove('hidden');
                    updateStatus('thinking', 'Memproses...');
                    changeAvatarVideo('thinking.mp4');
                    elements.avatarPulse.style.opacity = '0.5';
                    elements.avatarPulse.classList.add('animate-ping');
                    break;
                    
                case 'speaking':
                    document.getElementById('speaking-state').classList.remove('hidden');
                    updateStatus('speaking', 'Berbicara...');
                    changeAvatarVideo('speaking.mp4');
                    elements.avatarPulse.style.opacity = '0.7';
                    elements.avatarPulse.classList.add('animate-ping');
                    break;
            }
            
            // Log state change for debugging
            console.log(`✅ State updated to: ${newState}`);
        }

        // Avatar video management with improved error handling
        function changeAvatarVideo(filename) {
            const newSrc = `{{ asset('videos/avatar/') }}/${filename}`;
            console.log('🎬 Changing avatar video to:', filename);
            
            // Update both avatar videos with error handling
            [elements.avatarVideo, elements.mainAvatarVideo].forEach((video, index) => {
                if (video) {
                    const videoName = index === 0 ? 'popup' : 'main';
                    console.log(`🎬 Updating ${videoName} avatar video`);
                    
                    // Set up error handler
                    video.onerror = function() {
                        console.warn(`❌ Failed to load ${videoName} avatar video: ${filename}`);
                        // Show fallback
                        this.style.display = 'none';
                        const fallback = this.nextElementSibling;
                        if (fallback && fallback.classList.contains('bg-gradient-to-br')) {
                            fallback.style.display = 'flex';
                        }
                    };
                    
                    // Set up success handler
                    video.onloadeddata = function() {
                        console.log(`✅ ${videoName} avatar video loaded: ${filename}`);
                        this.style.display = 'block';
                        const fallback = this.nextElementSibling;
                        if (fallback && fallback.classList.contains('bg-gradient-to-br')) {
                            fallback.style.display = 'none';
                        }
                    };
                    
                    // Only update if source is different
                    if (video.src !== newSrc) {
                        video.src = newSrc;
                    }
                    
                    // Force reload if video is not playing
                    if (video.paused || video.ended) {
                        video.load();
                        video.play().catch(e => {
                            console.warn(`Could not autoplay ${videoName} avatar video:`, e);
                        });
                    }
                }
            });
        }

        // Update status dengan validasi yang lebih kuat
        function updateStatus(type, message) {
            // Gunakan validation yang lebih kuat
            const sanitizedMessage = sanitizeText(message);
            
            if (elements.statusText) {
                elements.statusText.textContent = sanitizedMessage;
                
                // Monitor untuk corruption setelah update
                setTimeout(() => monitorTextCorruption(), 100);
            }
            
            // Update status indicator
            if (elements.statusIndicator) {
                elements.statusIndicator.className = elements.statusIndicator.className.replace(/status-\w+/g, '');
                
                switch (type) {
                    case 'ready':
                        elements.statusIndicator.classList.add('status-online');
                        break;
                    case 'thinking':
                    case 'processing':
                        elements.statusIndicator.classList.add('status-busy');
                        break;
                    case 'speaking':
                        elements.statusIndicator.classList.add('status-online');
                        break;
                    case 'listening':
                        elements.statusIndicator.classList.add('status-online');
                        break;
                    default:
                        elements.statusIndicator.classList.add('status-online');
                }
            }
        }

        // Fungsi untuk membersihkan text aneh
        function sanitizeText(text) {
            if (!text || typeof text !== 'string') {
                return 'Online';
            }
            
            // Hapus karakter aneh dan non-printable
            let cleaned = text.replace(/[\x00-\x1F\x7F-\x9F]/g, '')
                             .replace(/[^\p{L}\p{N}\p{P}\p{Z}]/gu, '')
                             .replace(/\s+/g, ' ')
                             .trim();
            
            // Check untuk pattern aneh
            const patterns = [
                /^[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]{15,}$/, // consonant clusters
                /^[aeiouAEIOU]{10,}$/, // vowel clusters  
                /(.)\1{10,}/, // repeated characters
                /[A-Z]{15,}/, // excessive caps
                /[a-z]{30,}/ // excessive lowercase
            ];
            
            for (const pattern of patterns) {
                if (pattern.test(cleaned)) {
                    console.warn('🚨 Suspicious text pattern detected:', cleaned);
                    return 'Online';
                }
            }
            
            // Length check
            if (cleaned.length > 30) {
                cleaned = cleaned.substring(0, 27) + '...';
            }
            
            // Final validation - harus mengandung karakter yang masuk akal
            if (!cleaned || !/[a-zA-Z\u00C0-\u017F]/.test(cleaned)) {
                return 'Online';
            }
            
            return cleaned;
        }

        // Monitoring untuk text corruption
        function monitorTextCorruption() {
            const statusElement = elements.statusText;
            if (!statusElement) return;
            
            const currentText = statusElement.textContent;
            
            // Deteksi pattern text yang aneh/corrupt
            const isCorrupted = /[^\p{L}\p{N}\p{P}\p{Z}\s]/gu.test(currentText) || 
                              currentText.length > 50 ||
                              /^[a-z]{20,}$/i.test(currentText) || // string random panjang
                              /[A-Z]{10,}/.test(currentText); // uppercase berlebihan
            
            if (isCorrupted) {
                console.warn('🚨 Text corruption detected, resetting status:', currentText);
                resetChatbotStatus();
            }
        }
        
        // Input handlers
        function handleInputChange() {
            updateCharCount();
        }

        function updateCharCount() {
            const length = elements.messageInput.value.length;
            elements.charCount.textContent = length;
            
            if (length > 450) {
                elements.charCount.classList.add('text-red-500');
            } else {
                elements.charCount.classList.remove('text-red-500');
            }
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        function handleKeyboardShortcuts(e) {
            if (e.ctrlKey && e.code === 'Space') {
                e.preventDefault();
                toggleVoiceInput();
            }
        }

        // Voice input with improved error handling
        function toggleVoiceInput() {
            if (!recognition) {
                showError('Speech recognition tidak didukung di browser ini. Gunakan Chrome, Edge, atau Safari versi terbaru.');
                return;
            }
            
            if (currentState === 'listening') {
                try {
                    recognition.stop();
                    setState('idle');
                    resetVoiceButton();
                } catch (error) {
                    console.error('Error stopping speech recognition:', error);
                    setState('idle');
                    resetVoiceButton();
                }
            } else if (currentState === 'idle') {
                try {
                    // Request microphone permission first
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ audio: true })
                            .then(() => {
                                recognition.start();
                            })
                            .catch((error) => {
                                console.error('Microphone permission denied:', error);
                                showError('Akses mikrofon ditolak. Silakan aktifkan izin mikrofon di browser.');
                            });
                    } else {
                        recognition.start();
                    }
                } catch (error) {
                    console.error('Failed to start speech recognition:', error);
                    showError('Gagal memulai pengenalan suara. Pastikan mikrofon tersedia.');
                    resetVoiceButton();
                }
            } else {
                showError('Sedang memproses. Tunggu sebentar.');
            }
        }

        // Send message with improved error handling and animation
        async function sendMessage() {
            const message = elements.messageInput.value.trim();
            
            if (!message || isProcessing) return;
            
            console.log('📤 Sending message:', message);
            
            isProcessing = true;
            setState('thinking');
            
            const startTime = Date.now();
            
            try {
                showLoading(true);
                
                // Add thinking animation to response area
                elements.responseText.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-blue-500 rounded-full loading-dot"></div>
                            <div class="w-2 h-2 bg-blue-500 rounded-full loading-dot"></div>
                            <div class="w-2 h-2 bg-blue-500 rounded-full loading-dot"></div>
                        </div>
                        <span class="text-sm text-gray-500">AI sedang menyiapkan jawaban...</span>
                    </div>
                `;
                elements.lastResponse.classList.remove('hidden');
                
                // Create AbortController for timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
                
                const response = await fetch(CONFIG.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: sessionId,
                        timestamp: Date.now()
                    }),
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('API Error Response:', errorText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                const responseTime = Date.now() - startTime;
                console.log(`📥 Response received in ${responseTime}ms:`, data);
                
                // Display response with typing animation
                const responseMessage = data.message || data.response || data.bot_response || 'Maaf, saya tidak dapat memproses permintaan Anda.';
                displayResponseWithAnimation(responseMessage);
                
                // Show response time
                showResponseTime(responseTime);
                
                // Clear input
                elements.messageInput.value = '';
                updateCharCount();
                
                // Speak response if enabled
                if (isSoundEnabled && responseMessage) {
                    speakText(responseMessage);
                } else {
                    setState('idle');
                }
                
            } catch (error) {
                console.error('❌ Error sending message:', error);
                
                let errorMessage = 'Maaf, terjadi kesalahan. Silakan coba lagi.';
                
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timeout. Server membutuhkan waktu terlalu lama. Silakan coba lagi.';
                } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                } else if (error.message.includes('500')) {
                    errorMessage = 'Server sedang bermasalah. Silakan coba beberapa saat lagi.';
                } else if (error.message.includes('404')) {
                    errorMessage = 'Layanan chatbot tidak ditemukan. Hubungi administrator.';
                } else if (error.message.includes('405')) {
                    errorMessage = 'Method tidak diizinkan. Ada masalah dengan konfigurasi server.';
                } else if (error.message.includes('422')) {
                    errorMessage = 'Data yang dikirim tidak valid. Silakan coba lagi.';
                } else if (error.message.includes('429')) {
                    errorMessage = 'Terlalu banyak permintaan. Silakan tunggu sebentar.';
                }
                
                showError(errorMessage);
                displayResponse('❌ ' + errorMessage);
                setState('idle');
            } finally {
                isProcessing = false;
                showLoading(false);
            }
        }

        // Display response with typing animation per 2 sentences
        function displayResponseWithAnimation(text) {
            elements.responseText.textContent = '';
            elements.lastResponse.classList.remove('hidden');
            
            // Split text into sentences
            const sentences = text.match(/[^\.!?]+[\.!?]+/g) || [text];
            
            // Group sentences into chunks of 2
            const chunks = [];
            for (let i = 0; i < sentences.length; i += 2) {
                const chunk = sentences.slice(i, i + 2).join(' ').trim();
                if (chunk) chunks.push(chunk);
            }
            
            let currentChunkIndex = 0;
            
            function typeNextChunk() {
                if (currentChunkIndex >= chunks.length) {
                    // Scroll to response when all typing is complete
                    elements.lastResponse.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    return;
                }
                
                const currentChunk = chunks[currentChunkIndex];
                const isFirstChunk = currentChunkIndex === 0;
                
                // Add separator if not first chunk
                if (!isFirstChunk) {
                    elements.responseText.textContent += ' ';
                }
                
                let chunkIndex = 0;
                const typeChunk = () => {
                    if (chunkIndex < currentChunk.length) {
                        elements.responseText.textContent += currentChunk.charAt(chunkIndex);
                        chunkIndex++;
                        setTimeout(typeChunk, 50); // Typing speed
                    } else {
                        // Wait before next chunk
                        currentChunkIndex++;
                        setTimeout(() => {
                            typeNextChunk();
                        }, 800); // Pause between chunks
                    }
                };
                
                typeChunk();
            }
            
            typeNextChunk();
        }

        // Display response (fallback for simple display)
        function displayResponse(text) {
            elements.responseText.textContent = text;
            elements.lastResponse.classList.remove('hidden');
            elements.lastResponse.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Text-to-speech with per-sentence animation (improved)
        function speakText(text) {
            if (!isSoundEnabled || !text.trim()) {
                setState('idle');
                return;
            }
            
            console.log('🔊 Speaking text:', text.substring(0, 50) + '...');
            
            // Cancel any ongoing speech
            speechSynthesis.cancel();
            
            setState('speaking');
            
            // Split text into sentences and group into chunks of 2
            const sentences = text.match(/[^\.!?]+[\.!?]+/g) || [text];
            const chunks = [];
            
            // Group sentences into chunks of 2
            for (let i = 0; i < sentences.length; i += 2) {
                const chunk = sentences.slice(i, i + 2).join(' ').trim();
                if (chunk) chunks.push(chunk);
            }
            
            let currentChunkIndex = 0;
            
            function speakNextChunk() {
                if (currentChunkIndex >= chunks.length) {
                    setState('idle');
                    hideSubtitle();
                    return;
                }
                
                const currentChunk = chunks[currentChunkIndex];
                console.log(`🔊 Speaking chunk ${currentChunkIndex + 1}/${chunks.length}:`, currentChunk.substring(0, 30) + '...');
                
                const utterance = new SpeechSynthesisUtterance(currentChunk);
                
                // Configure voice
                const voices = speechSynthesis.getVoices();
                const indonesianVoice = voices.find(voice => 
                    voice.lang.includes('id') || voice.lang.includes('ID')
                ) || voices[0];
                
                if (indonesianVoice) {
                    utterance.voice = indonesianVoice;
                    console.log('🔊 Using voice:', indonesianVoice.name);
                }
                
                utterance.rate = CONFIG.voiceRate;
                utterance.pitch = CONFIG.voicePitch;
                utterance.volume = 1;
                
                utterance.onend = function() {
                    console.log(`🔊 Chunk ${currentChunkIndex + 1} ended`);
                    currentChunkIndex++;
                    
                    // Wait a bit before next chunk
                    setTimeout(() => {
                        speakNextChunk();
                    }, 700); // Slightly longer pause between chunks
                };
                
                utterance.onerror = function(error) {
                    console.error('🔊 Speech error:', error);
                    setState('idle');
                    hideSubtitle();
                    showError('Gagal memutar suara. Coba aktifkan speaker.');
                };
                
                currentUtterance = utterance;
                speechSynthesis.speak(utterance);
            }
            
            // Start speaking
            speakNextChunk();
        }
        
        // Create subtitle container if it doesn't exist
        function createSubtitleContainer() {
            let container = document.getElementById('subtitle-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'subtitle-container';
                container.className = 'fixed bottom-32 left-1/2 transform -translate-x-1/2 z-40 hidden';
                document.body.appendChild(container);
            }
            return container;
        }
        
        // Hide subtitle
        function hideSubtitle() {
            const subtitleContainer = document.getElementById('subtitle-container');
            if (subtitleContainer) {
                subtitleContainer.classList.add('hidden');
            }
        }

        function stopCurrentSpeech() {
            if (currentUtterance) {
                speechSynthesis.cancel();
                currentUtterance = null;
                setState('idle');
                hideSubtitle();
                console.log('🔊 Speech stopped');
            }
        }

        // UI helpers
        function showLoading(show) {
            elements.loadingOverlay.classList.toggle('hidden', !show);
            elements.loadingOverlay.classList.toggle('flex', show);
        }

        function showError(message) {
            console.error('❌', message);
            
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }

        function showResponseTime(ms) {
            const seconds = (ms / 1000).toFixed(1);
            if (elements.responseTime && elements.responseTime.querySelector('span')) {
                elements.responseTime.querySelector('span').textContent = `${seconds}s`;
                elements.responseTime.classList.remove('hidden');
                
                setTimeout(() => {
                    if (elements.responseTime) {
                        elements.responseTime.classList.add('hidden');
                    }
                }, 3000);
            }
        }

        // Theme management
        function toggleTheme() {
            isDarkMode = !isDarkMode;
            localStorage.setItem('darkMode', isDarkMode);
            updateTheme();
        }

        function updateTheme() {
            document.documentElement.classList.toggle('dark', isDarkMode);
            
            // Update main theme toggle button icon
            const mainThemeButton = document.getElementById('main-theme-toggle');
            if (mainThemeButton) {
                const icon = mainThemeButton.querySelector('svg');
                if (isDarkMode) {
                    // Light mode icon (sun)
                    icon.innerHTML = '<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />';
                } else {
                    // Dark mode icon (moon)
                    icon.innerHTML = '<path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />';
                }
            }
            
            // Update chatbot theme toggle icons
            const darkModeIcon = document.getElementById('dark-mode-icon');
            const lightModeIcon = document.getElementById('light-mode-icon');
            
            if (darkModeIcon && lightModeIcon) {
                if (isDarkMode) {
                    darkModeIcon.classList.add('hidden');
                    lightModeIcon.classList.remove('hidden');
                } else {
                    darkModeIcon.classList.remove('hidden');
                    lightModeIcon.classList.add('hidden');
                }
            }
            
            console.log('🌙 Theme updated:', isDarkMode ? 'Dark' : 'Light');
        }

        // Clear chat function
        function clearChat() {
            elements.messageInput.value = '';
            elements.lastResponse.classList.add('hidden');
            elements.responseText.textContent = '';
            updateCharCount();
            
            // Stop any ongoing speech
            stopCurrentSpeech();
            
            setState('idle');
            console.log('🗑️ Chat cleared');
        }
        
        // Sound management
        function toggleSound() {
            isSoundEnabled = !isSoundEnabled;
            localStorage.setItem('soundEnabled', isSoundEnabled);
            updateSoundState();
            
            if (!isSoundEnabled && currentUtterance) {
                stopCurrentSpeech();
            }
            
            console.log('🔊 Sound toggled:', isSoundEnabled ? 'ON' : 'OFF');
        }

        function updateSoundState() {
            const soundOnIcon = document.getElementById('sound-on-icon');
            const soundOffIcon = document.getElementById('sound-off-icon');
            const soundToggleBtn = document.getElementById('sound-toggle-btn');
            
            if (soundOnIcon && soundOffIcon && soundToggleBtn) {
                if (isSoundEnabled) {
                    soundOnIcon.classList.remove('hidden');
                    soundOffIcon.classList.add('hidden');
                    soundToggleBtn.title = 'Disable Sound';
                } else {
                    soundOnIcon.classList.add('hidden');
                    soundOffIcon.classList.remove('hidden');
                    soundToggleBtn.title = 'Enable Sound';
                }
            }
            
            console.log('🔊 Sound state updated:', isSoundEnabled);
        }

        // Chatbot popup management
        let isChatbotOpen = false;
        
        function toggleChatbot() {
            console.log('🎮 Toggle chatbot clicked, current state:', isChatbotOpen);
            
            const popup = document.getElementById('chatbot-popup');
            const chatIcon = document.getElementById('chat-icon');
            const closeIcon = document.getElementById('close-icon');
            
            if (!popup || !chatIcon || !closeIcon) {
                console.error('❌ Chatbot elements not found');
                return;
            }
            
            isChatbotOpen = !isChatbotOpen;
            
            if (isChatbotOpen) {
                popup.classList.remove('hidden');
                chatIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
                console.log('✅ Chatbot opened');
            } else {
                popup.classList.add('hidden');
                chatIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                console.log('✅ Chatbot closed');
            }
        }
        
        // Main theme toggle for the header
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎮 DOM Content Loaded');
            
            // Theme toggle
            const mainThemeToggle = document.getElementById('main-theme-toggle');
            if (mainThemeToggle) {
                console.log('✅ Theme toggle button found');
                mainThemeToggle.addEventListener('click', function() {
                    console.log('🌙 Theme toggle clicked');
                    toggleTheme();
                });
            } else {
                console.warn('⚠️ Theme toggle button not found');
            }
            
            // Make sure functions are available globally
            window.toggleChatbot = toggleChatbot;
            window.toggleSound = toggleSound;
            window.toggleTheme = toggleTheme;
            
            console.log('🎮 Global functions registered');
        });
        
        // Load voices when available (for better TTS support)
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = function() {
                const voices = speechSynthesis.getVoices();
                console.log('🔊 Available voices:', voices.length);
                
                // Log Indonesian voices if available
                const indonesianVoices = voices.filter(voice => 
                    voice.lang.includes('id') || voice.lang.includes('ID')
                );
                if (indonesianVoices.length > 0) {
                    console.log('🔊 Indonesian voices found:', indonesianVoices.map(v => v.name));
                } else {
                    console.log('🔊 No Indonesian voices found, will use default voice');
                }
            };
        }
        
        // Error handling
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });

        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
        });

        console.log('🚀 Chatbot script loaded successfully');
        console.log('🎮 Features enabled:');
        console.log('  - Speech Recognition:', 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window);
        console.log('  - Text-to-Speech:', 'speechSynthesis' in window);
        console.log('  - Sound:', isSoundEnabled);
        console.log('  - Dark Mode:', isDarkMode);
        console.log('  - API Endpoint:', CONFIG.apiEndpoint);
        console.log('  - CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ? 'Available' : 'Missing');
        
        // Test API connectivity on load
        setTimeout(() => {
            console.log('🔍 Testing API connectivity...');
            fetch(CONFIG.apiEndpoint, {
                method: 'OPTIONS',
                headers: {
                    'Accept': 'application/json'
                }
            }).then(response => {
                console.log('✅ API endpoint accessible:', response.status);
            }).catch(error => {
                console.warn('⚠️ API endpoint test failed:', error.message);
            });
        }, 2000);
    </script>

    <!-- Footer -->
    <footer class="relative z-10 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border-t border-gray-200 dark:border-gray-700 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">AI Assistant</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Experience the future of conversation with our advanced AI chatbot. Get instant answers, creative assistance, and intelligent support.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-500 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-500 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-500 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Features</h4>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Voice Recognition</a></li>
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Text to Speech</a></li>
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Multi-language</a></li>
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Smart Responses</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Support</h4>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Documentation</a></li>
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-200 dark:border-gray-700 mt-8 pt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    © 2025 AI Assistant. All rights reserved. Powered by advanced AI technology.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
