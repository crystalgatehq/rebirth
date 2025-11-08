<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div x-data="{ activeTab: 'profile' }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8 relative">
            <!-- Background Image with Blur -->
            <div class="absolute inset-0 overflow-hidden">
                <img src="https://accounts.ecitizen.go.ke/en/images/banner-1.jpg" 
                     alt="Background" 
                     class="w-full h-full object-cover filter blur-sm brightness-50">
                <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/30 to-black/50"></div>
            </div>
            
            <!-- Content Container -->
            <div class="relative p-6 md:p-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between w-full gap-6 lg:gap-0">
                    <!-- Left Side - Profile Info -->
                    <div class="flex items-center space-x-4 md:space-x-6">
                        <div class="relative flex-shrink-0">
                            <img class="h-16 w-16 md:h-20 md:w-20 rounded-full ring-4 ring-white/80 shadow-xl" 
                                 src="{{ Auth::user()->profile_photo_url }}" 
                                 alt="{{ Auth::user()->name }}">
                            <span class="absolute bottom-0 right-0 block h-4 w-4 rounded-full bg-green-400 ring-2 ring-white shadow-sm"></span>
                        </div>

                        <div class="flex-shrink-0">
                            <div class="space-y-2">
                                <div>
                                    <h1 class="text-xl md:text-2xl font-bold text-white drop-shadow-xl">{{ Auth::user()->name }}</h1>
                                    <p class="text-sm text-gray-100 font-medium mt-0.5">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="inline-flex items-center px-3 py-1.5 bg-green-600/90 backdrop-blur-sm rounded-full shadow-md border border-white/10">
                                    <span class="relative flex h-2.5 w-2.5 mr-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white/80"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-white tracking-wide">ACTIVE NOW</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Action Buttons and Social Stats -->
                    <div class="flex-shrink-0 flex flex-col items-start lg:items-end w-full lg:w-auto lg:ml-4">
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2 md:gap-3 mb-4 w-full sm:w-auto">
                            <button class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all">
                                <svg class="-ml-0.5 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Message
                            </button>
                            <button class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md transition-all">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Follow
                            </button>
                            <button class="inline-flex items-center justify-center p-2 border border-gray-200 rounded-lg text-gray-500 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Social Stats -->
                        <div class="flex justify-between w-full gap-3 md:gap-4 lg:gap-6 lg:w-auto">
                            <div class="flex-1 lg:flex-none text-center px-3 md:px-4 py-2.5 bg-white/95 backdrop-blur-sm rounded-lg shadow-md border border-white/50">
                                <span class="block text-sm md:text-base font-bold text-gray-900">1,234</span>
                                <span class="text-xs font-medium text-gray-600">Posts</span>
                            </div>
                            <div class="flex-1 lg:flex-none text-center px-3 md:px-4 py-2.5 bg-white/95 backdrop-blur-sm rounded-lg shadow-md border border-white/50">
                                <span class="block text-sm md:text-base font-bold text-gray-900">5.6k</span>
                                <span class="text-xs font-medium text-gray-600">Followers</span>
                            </div>
                            <div class="flex-1 lg:flex-none text-center px-3 md:px-4 py-2.5 bg-white/95 backdrop-blur-sm rounded-lg shadow-md border border-white/50">
                                <span class="block text-sm md:text-base font-bold text-gray-900">1.2k</span>
                                <span class="text-xs font-medium text-gray-600">Following</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-t border-gray-200 bg-white relative z-10">
                <nav class="flex overflow-x-auto">
                    <button @click="activeTab = 'profile'" 
                            :class="{ 'border-blue-500 text-blue-600 bg-blue-50': activeTab === 'profile', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'profile' }"
                            class="px-6 py-4 text-sm font-medium border-b-2 focus:outline-none whitespace-nowrap transition-colors">
                        <i class="fas fa-user-circle mr-2"></i> Profile
                    </button>
                    <button @click="activeTab = 'security'" 
                            :class="{ 'border-blue-500 text-blue-600 bg-blue-50': activeTab === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'security' }"
                            class="px-6 py-4 text-sm font-medium border-b-2 focus:outline-none whitespace-nowrap transition-colors">
                        <i class="fas fa-shield-alt mr-2"></i> Security
                    </button>
                    <button @click="activeTab = 'sessions'" 
                            :class="{ 'border-blue-500 text-blue-600 bg-blue-50': activeTab === 'sessions', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'sessions' }"
                            class="px-6 py-4 text-sm font-medium border-b-2 focus:outline-none whitespace-nowrap transition-colors">
                        <i class="fas fa-laptop mr-2"></i> Sessions
                    </button>
                    <button @click="activeTab = 'danger'" 
                            :class="{ 'border-red-500 text-red-600 bg-red-50': activeTab === 'danger', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'danger' }"
                            class="px-6 py-4 text-sm font-medium border-b-2 focus:outline-none whitespace-nowrap transition-colors">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Danger Zone
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="space-y-6">
            <!-- Profile Information -->
            <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                    <div class="p-6">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Security -->
            <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
                <!-- Update Password -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                    <div class="p-6">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Two Factor Authentication -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                    <div class="p-6">
                        @include('profile.partials.two-factor-authentication-form')
                    </div>
                </div>
            </div>

            <!-- Sessions -->
            <div x-show="activeTab === 'sessions'" x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                    <div class="p-6">
                        @include('profile.partials.browser-sessions')
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div x-show="activeTab === 'danger'" x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-red-50">
                    <div class="p-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>