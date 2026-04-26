<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PayChat - Revolutionary POS & Business Management Platform. Unified solution for real-time orders, inventory management, and payments.">
    <title>PayChat - Revolutionary POS & Business Management Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#6366f1',
                        'primary-dark': '#4f46e5',
                        'secondary': '#f59e0b',
                        'accent': '#10b981',
                        'navy': '#1e293b',
                        'cream': '#fef7ed'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom animations */
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .fade-in-up.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        /* Gradient backgrounds */
        .gradient-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        
        .gradient-secondary {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        }
        
        /* Custom shadows */
        .shadow-custom {
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.1);
        }
        
        .shadow-custom-lg {
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.15);
        }
        
        /* Coming Soon Badge */
        .coming-soon-badge {
            background: linear-gradient(45deg, #f59e0b, #f97316);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { box-shadow: 0 0 10px rgba(245, 158, 11, 0.5); }
            to { box-shadow: 0 0 20px rgba(249, 115, 22, 0.8); }
        }
        
        /* App-focused styling */
        .app-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <img src="color-paychat-logo-main.svg" alt="PayChat Logo" class="h-10 lg:h-12 w-auto">
                    <div class="flex items-center space-x-2">
                        <span class="coming-soon-badge text-white text-xs px-3 py-1 rounded-full font-semibold">
                            Early Customers Only
                        </span>                    
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#features" class="text-navy hover:text-primary font-medium transition-colors">Features</a>
                    <a href="#benefits" class="text-navy hover:text-primary font-medium transition-colors">Why PayChat</a>
                    <a href="#scanner" class="text-navy hover:text-primary font-medium transition-colors">QR Scanner</a>
                    <a href="#roadmap" class="text-navy hover:text-primary font-medium transition-colors">Roadmap</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center space-x-3">
                   
                  
                    <!-- <a href="login.html" class="bg-accent hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 text-sm lg:text-base">
                        Shop Login
                    </a> -->
                    
                    <!-- Mobile Menu Button -->
                    <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-md text-navy hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <a 
                    href="#book-demo" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition text-sm lg:text-base"
                    >
                    Book Demo
                    </a>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="lg:hidden hidden border-t border-gray-200 py-4">
                <div class="flex flex-col space-y-3">
                    <a href="#features" class="text-navy hover:text-primary font-medium py-2">Features</a>
                    <a href="#benefits" class="text-navy hover:text-primary font-medium py-2">Why PayChat</a>
                    <a href="#scanner" class="text-navy hover:text-primary font-medium py-2">QR Scanner</a>
                    <a href="#roadmap" class="text-navy hover:text-primary font-medium py-2">Roadmap</a>
                    <a href="login.html" class="text-accent hover:text-green-600 font-medium py-2">Shop Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-primary text-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center mb-6">
                    <span class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-full font-semibold text-sm flex items-center">
                        ⭐ Early Customer Access
                    </span>
                </div>
                
                <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
                    PayChat: Revolutionary POS & 
                    <span class="text-secondary">Business Management</span> 
                    Platform
                </h1>
                <p class="text-xl lg:text-2xl text-blue-100 mb-8 leading-relaxed max-w-4xl mx-auto">
                    A unified solution that processes real-time orders, manages inventory, and handles payments—all from the point of sale or through our mobile app. Designed for small outlets like retail stores, restaurants, salons, and appointment-based businesses.
                </p>
                
                <!-- Waitlist Form -->
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-8 mb-8 max-w-md mx-auto border border-white border-opacity-20">
                    <h3 class="text-xl font-semibold mb-6">Join the Waitlist</h3>
                    <div class="space-y-4">
                        <a href="#book-demo"
                        class="gradient-secondary text-white px-8 py-4 rounded-xl font-semibold text-lg hover:shadow-xl transition-all duration-300">
                            Book A Demo
                        </a>
                    </div>
                    <p class="text-blue-200 text-sm mt-4">Be the first to know when we launch. No spam, ever.</p>
                </div>
                
                <!-- Launch Stats -->
                <div class="grid grid-cols-3 gap-8 pt-8 border-t border-blue-300 border-opacity-30 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-secondary" id="waitlistCount">10+</div>
                        <div class="text-blue-100 text-sm lg:text-base">On Waitlist</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-secondary">Live Soon</div>
                        <div class="text-blue-100 text-sm lg:text-base">Early Access Phase</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-secondary">Founding</div>
                        <div class="text-blue-100 text-sm lg:text-base">Early Customers</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Demo Video Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center px-6">

            <h2 class="text-3xl font-bold text-navy mb-6">See PayChat in Action</h2>
            <p class="text-gray-600 mb-10">
            Watch how PayChat handles orders, payments, and inventory in real-time.
            </p>

            <div class="rounded-2xl overflow-hidden shadow-lg">
            <iframe 
                class="w-full h-64 md:h-96"
                src="https://www.youtube.com/embed/n6fcyBb1LZk"
                title="PayChat Demo"
                frameborder="0"
                allowfullscreen>
            </iframe>
            </div>

        </div>
    </section>
    <!-- QR Scanner Section -->
    <section id="scanner" class="py-16 lg:py-20 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-6">Try Our QR Scanner</h2>
            <p class="text-xl text-gray-600 mb-12">Experience the future of instant access. Scan any QR code to see how PayChat will work.</p>
            
            <div class="bg-white rounded-3xl p-8 lg:p-12 shadow-custom-lg">
                <div class="max-w-md mx-auto">
                    <button onclick="startScanner()" class="w-32 h-32 lg:w-40 lg:h-40 gradient-primary rounded-3xl flex items-center justify-center mx-auto mb-8 hover:shadow-xl transition-all duration-300 scanner-pulse">
                        <svg class="w-16 h-16 lg:w-20 lg:h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4m6-4h.01M12 8h.01M12 8h4.01M12 8H7.99"/>
                        </svg>
                    </button>
                    
                    <h3 class="text-2xl font-bold text-navy mb-4">Tap to Scan</h3>
                    <p class="text-gray-600 mb-6">Point your camera at any QR code to test our scanning technology. Try scanning a website QR code!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-6">Complete Business Management Platform</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Everything you need to run your business efficiently, from POS to mobile app integration</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Product & Inventory Management -->
                <div class="bg-white rounded-2xl p-8 shadow-custom hover:shadow-custom-lg transition-all duration-300 fade-in-up">
                    <div class="w-16 h-16 gradient-primary rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-navy mb-4">Product & Inventory Management</h3>
                    <p class="text-gray-600 mb-4">User-friendly dashboard to add and edit products or services. Real-time inventory tracking automatically adjusts stock counts to prevent overselling.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Easy product management dashboard</li>
                        <li>• Real-time stock tracking</li>
                        <li>• Automatic restock alerts</li>
                        <li>• Sales-based inventory updates</li>
                    </ul>
                </div>

                <!-- POS Screen & QR-based Order Entry -->
                <div class="bg-white rounded-2xl p-8 shadow-custom hover:shadow-custom-lg transition-all duration-300 fade-in-up">
                    <div class="w-16 h-16 bg-secondary rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4m6-4h.01M12 8h.01M12 8h4.01M12 8H7.99"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-navy mb-4">POS Screen & QR Order Entry</h3>
                    <p class="text-gray-600 mb-4">Advanced POS interface with barcode and QR scanning capabilities. Accept multiple order types with speed and accuracy.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Barcode & QR code scanning</li>
                        <li>• Multiple order types support</li>
                        <li>• Instant product data retrieval</li>
                        <li>• Fast checkout process</li>
                    </ul>
                </div>

                <!-- Token System (Queue Management) -->
                <div class="bg-white rounded-2xl p-8 shadow-custom hover:shadow-custom-lg transition-all duration-300 fade-in-up">
                    <div class="w-16 h-16 bg-accent rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-navy mb-4">QR-Based Token System</h3>
                    <p class="text-gray-600 mb-4">Smart queue management system. Customers receive unique QR tokens to track their turn—perfect for restaurants, clinics, and retail.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• QR-based queue tokens</li>
                        <li>• Real-time queue tracking</li>
                        <li>• Customer turn notifications</li>
                        <li>• Ideal for service businesses</li>
                    </ul>
                </div>

                <!-- Paperless Invoicing & Payment Links -->
                <div class="bg-white rounded-2xl p-8 shadow-custom hover:shadow-custom-lg transition-all duration-300 fade-in-up">
                    <div class="w-16 h-16 bg-purple-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-navy mb-4">Paperless Invoicing</h3>
                    <p class="text-gray-600 mb-4">Send invoices via mobile app or messaging platforms. Customers receive receipts directly after payment—completely paperless process.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Digital invoice delivery</li>
                        <li>• Multiple delivery channels</li>
                        <li>• No printing required</li>
                        <li>• Integrated payment flow</li>
                    </ul>
                </div>

                <!-- Merchant Dashboard -->
                <div class="bg-white rounded-2xl p-8 shadow-custom hover:shadow-custom-lg transition-all duration-300 fade-in-up">
                    <div class="w-16 h-16 bg-indigo-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-navy mb-4">Merchant Dashboard</h3>
                    <p class="text-gray-600 mb-4">Comprehensive business insights with daily and monthly analytics. Manage team members, generate reports, and configure settings.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Business analytics & insights</li>
                        <li>• Team management & access control</li>
                        <li>• Customizable reports</li>
                        <li>• POS settings & preferences</li>
                    </ul>
                </div>

                <!-- Mobile App & Integrations -->
                <div class="bg-white rounded-2xl p-8 shadow-custom hover:shadow-custom-lg transition-all duration-300 fade-in-up">
                    <div class="w-16 h-16 app-gradient rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-navy mb-4">Mobile App & Integrations</h3>
                    <p class="text-gray-600 mb-4">Accept orders through our mobile app and integrate with popular messaging platforms. Customers can browse catalogs, place orders, and make payments seamlessly.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Native mobile app ordering</li>
                        <li>• Product catalog sharing</li>
                        <li>• Messaging platform integration</li>
                        <li>• Multi-channel communication</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Journey Flow -->
    <section class="py-16 lg:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-6">How PayChat Works</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Simple, seamless customer journey from order to payment</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 gradient-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">1</span>
                    </div>
                    <h3 class="text-lg font-bold text-navy mb-2">Setup Products</h3>
                    <p class="text-gray-600 text-sm">Merchant sets up products and inventory in the dashboard</p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 gradient-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">2</span>
                    </div>
                    <h3 class="text-lg font-bold text-navy mb-2">Customer Orders</h3>
                    <p class="text-gray-600 text-sm">Customer scans QR, visits outlet, or orders via mobile app</p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-accent rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">3</span>
                    </div>
                    <h3 class="text-lg font-bold text-navy mb-2">Invoice & Payment</h3>
                    <p class="text-gray-600 text-sm">Receives invoice digitally and pays through the app</p>
                </div>
                
                <!-- Step 4 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">4</span>
                    </div>
                    <h3 class="text-lg font-bold text-navy mb-2">Real-time Updates</h3>
                    <p class="text-gray-600 text-sm">Merchant sees updates, receipt delivered automatically</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-6">Why Choose PayChat?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">All-in-one solution designed for modern businesses</p>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Benefits List -->
                <div class="space-y-8">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 gradient-primary rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-navy mb-2">All-in-One Platform</h3>
                            <p class="text-gray-600">Sales, inventory, invoicing, payments, token systems—everything under one roof. No need for multiple apps or systems.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 app-gradient rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-navy mb-2">Native Mobile Experience</h3>
                            <p class="text-gray-600">Purpose-built mobile app with seamless user experience. Fast, reliable, and designed specifically for business operations.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-accent rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-navy mb-2">Real-Time Control</h3>
                            <p class="text-gray-600">Inventory, tokens, orders, and analytics update instantly as your business flows throughout the day.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 gradient-secondary rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-navy mb-2">Scalable for Small Businesses</h3>
                            <p class="text-gray-600">Perfect for retail, F&B, services, and appointment-based businesses across all industries.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Visual Element -->
                <div class="flex justify-center">
                    <div class="relative">
                        <div class="w-80 h-80 lg:w-96 lg:h-96 bg-white rounded-3xl shadow-custom-lg flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-24 h-24 gradient-primary rounded-3xl flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-navy mb-2">Complete Solution</h3>
                                <p class="text-gray-600">Everything your business needs in one platform</p>
                            </div>
                        </div>
                        
                        <!-- Floating Elements -->
                        <div class="absolute -top-4 -right-4 bg-accent text-white px-4 py-2 rounded-xl font-bold shadow-lg">
                            Mobile Ready
                        </div>
                        <div class="absolute -bottom-4 -left-4 bg-secondary text-white px-4 py-2 rounded-xl font-bold shadow-lg">
                            Real-Time
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Book Demo Section -->
    <section class="py-16 bg-white" id="book-demo">
    <div class="max-w-3xl mx-auto text-center px-6">

        <h2 class="text-3xl font-bold text-navy mb-4">Book a Free Demo</h2>
        <p class="text-gray-600 mb-8">
        See how PayChat can run your business in just 15 minutes.
        </p>

        <div class="bg-gray-50 p-8 rounded-2xl shadow">

        <input id="demoName" placeholder="Your Name" class="w-full p-4 mb-4 border rounded-xl">
        <input id="demoPhone" placeholder="Phone Number" class="w-full p-4 mb-4 border rounded-xl">
        <input id="demoBusiness" placeholder="Business Name" class="w-full p-4 mb-4 border rounded-xl">
        <input 
        type="datetime-local" 
        id="demoDate" 
        min=""
        class="w-full p-4 mb-4 border rounded-xl"
        />
        <button onclick="submitDemo()" 
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold">
            Book My Demo →
        </button>

        </div>
    </div>
    </section>
    <!-- CTA Section -->
    <section class="py-16 lg:py-20 gradient-primary text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold mb-6">Ready to Transform Your Business?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">Join our waitlist and be among the first to experience the future of business management. Early adopters get exclusive benefits.</p>
            
            <!-- Enhanced Waitlist Form -->
           
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-2xl font-bold text-secondary mb-2">✓ Early Access</div>
                    <div class="text-blue-100">Beta testing invitation</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-secondary mb-2">✓ Priority Support</div>
                    <div class="text-blue-100">Direct line to founders</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-secondary mb-2">✓ Exclusive Features</div>
                    <div class="text-blue-100">First access to new features</div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">

            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-navy">Live Partner Businesses</h2>
                <p class="text-gray-600">Discover shops using PayChat (early pilot network)</p>
            </div>

            <!-- Search -->
            <div class="max-w-md mx-auto mb-8">
                <input 
                    placeholder="Search shops..." 
                    class="w-full border p-4 rounded-xl"
                >
            </div>

            <!-- Tenant Grid -->
            <div id="tenantsGrid" class="grid md:grid-cols-3 gap-6">
                <div id="tenantsGrid">
                    <p>Loading partner businesses...</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-navy text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="color-paychat-logo-main.svg" alt="PayChat Logo" class="h-12 w-auto filter brightness-0 invert">
                        <div>
                            <p class="text-blue-200 text-sm">Launching Q4 2025</p>
                        </div>
                    </div>
                    <p class="text-blue-200 mb-6 max-w-md">Revolutionary POS & business management platform. Simplify operations, reduce paper, manage queues smartly, and engage customers through our mobile app—all from one dashboard.</p>
                    
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-blue-200 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#benefits" class="text-blue-200 hover:text-white transition-colors">Why PayChat</a></li>
                        <li><a href="#scanner" class="text-blue-200 hover:text-white transition-colors">QR Scanner</a></li>
                        <li><a href="login.html" class="text-blue-200 hover:text-white transition-colors">Shop Login</a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">Press Kit</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-blue-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-blue-200 text-sm">© 2025 PayChat. All rights reserved. Launching Q2 2025.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-blue-200 hover:text-white text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-blue-200 hover:text-white text-sm transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scanner Modal -->
    <div id="scannerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 gradient-primary rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4m6-4h.01M12 8h.01M12 8h4.01M12 8H7.99"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-navy mb-4">QR Code Scanner</h3>
            <p class="text-gray-600 mb-8">Point your camera at any QR code to test our scanning technology</p>
            
            <!-- Scanner Frame -->
            <div class="relative w-64 h-64 mx-auto mb-8 bg-gray-100 rounded-2xl overflow-hidden">
                <div id="qr-reader" class="w-64 h-64 mx-auto rounded-2xl overflow-hidden"></div>
            </div>
            
            <div class="space-y-4">
                <button onclick="simulateScan()" class="w-full gradient-primary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all duration-300">
                    Demo Scan (Test)
                </button>
                <button onclick="closeScannerModal()" class="w-full bg-gray-100 text-gray-600 font-semibold py-3 px-6 rounded-xl hover:bg-gray-200 transition-all duration-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 bg-accent rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-navy mb-4">QR Code Detected!</h3>
            <p class="text-gray-600 mb-6">We found a valid link. Would you like to visit it?</p>
            <div class="bg-cream rounded-xl p-4 mb-8">
                <p class="text-sm text-gray-500 mb-1">Detected URL:</p>
                <p class="text-navy font-mono text-sm break-all" id="detectedUrl">https://example.com</p>
            </div>
            <div class="space-y-3">
                <button onclick="confirmRedirect()" class="w-full gradient-secondary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all duration-300">
                    Visit Link
                </button>
                <button onclick="closeSuccessModal()" class="w-full bg-gray-100 text-gray-600 font-semibold py-3 px-6 rounded-xl hover:bg-gray-200 transition-all duration-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Waitlist Success Modal -->
    <div id="waitlistModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 bg-accent rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-navy mb-4">Welcome to PayChat!</h3>
            <p class="text-gray-600 mb-6">You're now on our exclusive waitlist. We'll notify you as soon as beta testing begins.</p>
            <div class="bg-cream rounded-xl p-4 mb-8">
                <p class="text-sm text-gray-500 mb-1">Your Position:</p>
                <p class="text-2xl font-bold text-primary" id="waitlistPosition">#50</p>
                <p class="text-sm text-gray-500 mt-2">Expected Beta Access: November 2025</p>
            </div>
            <button onclick="closeWaitlistModal()" class="w-full gradient-secondary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all duration-300">
                Got it!
            </button>
        </div>
    </div>

    <script>

function submitDemo() {
  const name = document.getElementById('demoName').value;
  const phone = document.getElementById('demoPhone').value;
  const business = document.getElementById('demoBusiness').value;
  const date = document.getElementById('demoDate').value;

  if (!name || !phone || !date) {
    alert("Please fill all required details");
    return;
  }

  const formattedDate = new Date(date).toLocaleString();

  const msg = `Hi PayChat, I want a demo.%0A
Name: ${name}%0A
Business: ${business}%0A
Phone: ${phone}%0A
Preferred Time: ${formattedDate}`;

  window.open(`https://wa.me/919834969229?text=${msg}`, '_blank');
}

// Set min date = now
document.addEventListener("DOMContentLoaded", () => {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

  document.getElementById('demoDate').min = now.toISOString().slice(0,16);
}); 
async function loadTenants() {
    try {
        const baseUrl = window.location.origin;

        const res = await fetch(`${baseUrl}/api/tenant/list`);
        const json = await res.json();

        const tenants = json?.data?.tenants || [];
        renderTenants(tenants);

    } catch (err) {
        console.error("Failed to load tenants:", err);
    }
}


function renderTenants(tenants) {
    const container = document.getElementById("tenantsGrid");

    if (!container) return;

    // If no tenants, show fallback UI (IMPORTANT for SEO + UX)
    if (!tenants || tenants.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-10">
                <h3 class="text-xl font-semibold text-gray-700">No partner businesses yet</h3>
                <p class="text-gray-500 mt-2">
                    PayChat is onboarding early merchants. Stay tuned!
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML = tenants.map(t => {

        const branding = t?.branding || {};

        const name = t?.name || "Unnamed Business";
        const industry = t?.industry || "Business";
        const address = branding?.address || "Partner Store";

        const logo = branding?.logo
            ? branding.logo
            : `https://dummyimage.com/200x60/ddd/000&text=${encodeURIComponent(name)}`;

        // safer API key handling
        const apiKey = t?.api_key || "";
        const baseUrl = window.location.origin || "http://localhost:8000";

        const shopUrl = `${baseUrl}/pos#/self-pos/${encodeURIComponent(apiKey)}`;

        return `
            <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition border border-gray-100">

                <!-- Header -->
                <div class="flex items-center gap-3 mb-3">
                    <img 
                        src="${logo}" 
                        alt="${name} logo"
                        class="h-10 w-10 object-cover rounded"
                        loading="lazy"
                    />

                    <div>
                        <h3 class="font-bold text-lg text-gray-900">${name}</h3>
                        <p class="text-gray-500 text-sm">${industry}</p>
                    </div>
                </div>

                <!-- Address -->
                <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                    ${address}
                </p>

                <!-- CTA -->
                <a 
                    href="${shopUrl}" 
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-xl font-semibold transition"
                >
                    Visit Shop →
                </a>
            </div>
        `;
    }).join("");
}

document.addEventListener("DOMContentLoaded", function () {
    loadTenants();
});
        let waitlistCount = 10;
        let detectedLink = null;

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        let qrScanner = null;

function startScanner() {
    const modal = document.getElementById('scannerModal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.querySelector('.bg-white').classList.remove('scale-95');
    modal.querySelector('.bg-white').classList.add('scale-100');

    setTimeout(() => {
        qrScanner = new Html5Qrcode("qr-reader");

        qrScanner.start(
            { facingMode: "environment" }, // back camera
            {
                fps: 10,
                qrbox: 250
            },
            (decodedText) => {
                detectedLink = decodedText;
                document.getElementById('detectedUrl').textContent = decodedText;

                stopScanner();
                simulateScan(); // reuse your success modal
            },
            (errorMessage) => {
                // ignore scan errors (normal while scanning)
            }
        );
    }, 300);
}

function stopScanner() {
    if (qrScanner) {
        qrScanner.stop().then(() => {
            qrScanner.clear();
            qrScanner = null;
        });
    }
}

function closeScannerModal() {
    stopScanner();

    const modal = document.getElementById('scannerModal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.querySelector('.bg-white').classList.add('scale-95');
    modal.querySelector('.bg-white').classList.remove('scale-100');
}

        function closeScannerModal() {
            const modal = document.getElementById('scannerModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('.bg-white').classList.add('scale-95');
            modal.querySelector('.bg-white').classList.remove('scale-100');
        }

        function simulateScan() {
            closeScannerModal();
            
            setTimeout(() => {
                const urls = [
                    'https://www.google.com',
                    'https://github.com',
                    'index.html',
                    'https://www.wikipedia.org'
                ];
                
                detectedLink = urls[Math.floor(Math.random() * urls.length)];
                document.getElementById('detectedUrl').textContent = detectedLink;
                
                const modal = document.getElementById('successModal');
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.querySelector('.bg-white').classList.remove('scale-95');
                modal.querySelector('.bg-white').classList.add('scale-100');
            }, 500);
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('.bg-white').classList.add('scale-95');
            modal.querySelector('.bg-white').classList.remove('scale-100');
        }

        function confirmRedirect() {
            if (detectedLink) {
                closeSuccessModal();
                setTimeout(() => {
                    if (detectedLink.startsWith('http')) {
                        window.open(detectedLink, '_blank');
                    } else {
                        window.location.href = detectedLink;
                    }
                }, 300);
            }
        }

        // Waitlist functionality
        function joinWaitlist() {
            const email = document.getElementById('waitlistEmail');
            if (email) {
                email.focus();
                email.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function submitWaitlist() {
            const email = document.getElementById('waitlistEmail').value;
            if (!email || !email.includes('@')) {
                alert('Please enter a valid email address');
                return;
            }
            
            processWaitlistSubmission();
        }

        function submitEnhancedWaitlist() {
            const businessName = document.getElementById('businessName').value;
            const businessEmail = document.getElementById('businessEmail').value;
            const businessType = document.getElementById('businessType').value;
            
            if (!businessName || !businessEmail || !businessEmail.includes('@') || !businessType) {
                alert('Please fill in all fields');
                return;
            }
            
            processWaitlistSubmission();
        }

        function processWaitlistSubmission() {
            const button = event.target;
            const originalText = button.textContent;
            button.innerHTML = '<div class="flex items-center justify-center"><div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>Joining...</div>';
            button.disabled = true;
            
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                
                // Update waitlist count
                waitlistCount++;
                document.getElementById('waitlistCount').textContent = waitlistCount + '+';
                
                // Show success modal
                document.getElementById('waitlistPosition').textContent = '#' + waitlistCount;
                const modal = document.getElementById('waitlistModal');
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.querySelector('.bg-white').classList.remove('scale-95');
                modal.querySelector('.bg-white').classList.add('scale-100');
                
                // Clear forms
                const emailInput = document.getElementById('waitlistEmail');
                const businessNameInput = document.getElementById('businessName');
                const businessEmailInput = document.getElementById('businessEmail');
                const businessTypeInput = document.getElementById('businessType');
                
                if (emailInput) emailInput.value = '';
                if (businessNameInput) businessNameInput.value = '';
                if (businessEmailInput) businessEmailInput.value = '';
                if (businessTypeInput) businessTypeInput.value = '';
            }, 2000);
        }

        function closeWaitlistModal() {
            const modal = document.getElementById('waitlistModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('.bg-white').classList.add('scale-95');
            modal.querySelector('.bg-white').classList.remove('scale-100');
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobileMenu');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        // Observe all fade-in-up elements
        document.querySelectorAll('.fade-in-up').forEach(el => {
            observer.observe(el);
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            const scannerModal = document.getElementById('scannerModal');
            const successModal = document.getElementById('successModal');
            const waitlistModal = document.getElementById('waitlistModal');
            
            if (e.target === scannerModal) {
                closeScannerModal();
            }
            
            if (e.target === successModal) {
                closeSuccessModal();
            }
            
            if (e.target === waitlistModal) {
                closeWaitlistModal();
            }
        });

        // Update waitlist count periodically (simulate real-time updates)
        setInterval(() => {
            if (Math.random() < 0.3) { // 30% chance every 10 seconds
                waitlistCount++;
                document.getElementById('waitlistCount').textContent = waitlistCount + '+';
            }
        }, 10000);

        // Initialize dynamic waitlist count
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate some initial activity
            const initialCount = 10 + Math.floor(Math.random() * 10);
            waitlistCount = initialCount;
            document.getElementById('waitlistCount').textContent = waitlistCount + '+';
        });

        function openEarlyBirdForm() {
            document.getElementById('earlyBirdModal').classList.remove('hidden');
        }

        function closeEarlyBirdForm() {
            document.getElementById('earlyBirdModal').classList.add('hidden');
        }
    </script>

    <script>
let step = 1;

function openEarlyBirdForm() {
  document.getElementById('earlyBirdModal').classList.remove('hidden');
  step = 1;
  updateUI();
}

function closeEarlyBirdForm() {
  document.getElementById('earlyBirdModal').classList.add('hidden');
}

function nextStep() {
  if (step < 3) {
    document.getElementById('step' + step).classList.add('hidden');
    step++;
    document.getElementById('step' + step).classList.remove('hidden');
    updateUI();
  }
}

function updateUI() {
  document.getElementById('stepCount').innerText = step;

  let progress = (step / 3) * 100;
  document.getElementById('progressBar').style.width = progress + '%';
}

function submitForm() {
  const data = {
    businessName: document.getElementById('businessName').value,
    ownerName: document.getElementById('ownerName').value,
    contactNumber: document.getElementById('contactNumber').value
  };

  console.log("Submitted:", data);

  document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
  document.getElementById('success').classList.remove('hidden');

  document.getElementById('stepCount').innerText = "Done";
  document.getElementById('progressBar').style.width = "100%";
}
</script>

<!-- Early Bird Typeform Style Modal -->
<div id="earlyBirdModal" class="fixed inset-0 bg-white z-50 hidden flex items-center justify-center">

  <div class="w-full max-w-2xl px-6 text-center">

    <!-- Progress -->
    <div class="mb-10">
      <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
        <div id="progressBar" class="h-full bg-indigo-500 w-1/3 transition-all duration-300"></div>
      </div>
      <p class="text-sm text-gray-400 mt-2">Step <span id="stepCount">1</span> of 3</p>
    </div>

    <!-- STEP 1 -->
    <div id="step1" class="step">
      <h1 class="text-3xl font-bold mb-6">What is your Business Name?</h1>
      <input id="businessName" class="w-full border p-4 rounded-xl text-lg" placeholder="e.g. Cafe Mocha">

      <button onclick="nextStep()" class="mt-8 bg-indigo-600 text-white px-8 py-4 rounded-xl text-lg">
        Next →
      </button>
    </div>

    <!-- STEP 2 -->
    <div id="step2" class="step hidden">
      <h1 class="text-3xl font-bold mb-6">Owner Name?</h1>
      <input id="ownerName" class="w-full border p-4 rounded-xl text-lg" placeholder="e.g. Archit">

      <button onclick="nextStep()" class="mt-8 bg-indigo-600 text-white px-8 py-4 rounded-xl text-lg">
        Next →
      </button>
    </div>

    <!-- STEP 3 -->
    <div id="step3" class="step hidden">
      <h1 class="text-3xl font-bold mb-6">Contact Number?</h1>
      <input id="contactNumber" class="w-full border p-4 rounded-xl text-lg" placeholder="+91 XXXXX XXXXX">

      <button onclick="submitForm()" class="mt-8 bg-green-600 text-white px-8 py-4 rounded-xl text-lg">
        Submit →
      </button>
    </div>

    <!-- SUCCESS -->
    <div id="success" class="hidden">
      <h1 class="text-3xl font-bold text-green-600 mb-4">You're in Early Access 🎉</h1>
      <p class="text-gray-500">We will contact you soon.</p>

      <button onclick="closeEarlyBirdForm()" class="mt-8 bg-gray-200 px-6 py-3 rounded-xl">
        Close
      </button>
    </div>

  </div>
</div>
<!-- WhatsApp Floating Button -->
<a 
  href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more%20about%20your%20POS"
  target="_blank"
  class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg z-50"
>
  <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
    <path d="M20.52 3.48A11.91 11.91 0 0012.02 0C5.38 0 .02 5.36.02 12c0 2.12.56 4.18 1.63 6L0 24l6.19-1.62A11.96 11.96 0 0012.02 24c6.64 0 12-5.36 12-12 0-3.19-1.24-6.18-3.5-8.52zM12 22c-1.9 0-3.75-.5-5.38-1.45l-.38-.23-3.67.96.98-3.58-.25-.37A9.94 9.94 0 012 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10zm5.17-7.36c-.28-.14-1.65-.82-1.9-.91-.25-.09-.43-.14-.61.14-.18.28-.7.91-.86 1.1-.16.18-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.36-.83-.74-1.4-1.65-1.57-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.32.42-.48.14-.16.18-.28.28-.46.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.83-2.01-.22-.53-.44-.46-.61-.47-.16-.01-.34-.01-.52-.01-.18 0-.48.07-.73.34-.25.28-.96.94-.96 2.29s.99 2.66 1.13 2.85c.14.18 1.96 2.99 4.75 4.19.66.28 1.18.44 1.58.56.66.21 1.26.18 1.73.11.53-.08 1.65-.67 1.88-1.32.23-.65.23-1.2.16-1.32-.07-.11-.25-.18-.53-.32z"/>
  </svg>
</a>
</body>
</html>
