<section class="space-y-6" x-data="{ showRecoveryCodesModal: false, showDisableModal: false }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 font-semibold">
            {{ __('Add additional security to your account using two factor authentication.') }}
        </p>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Authenticator application.') }}
        </p>
    </header>

    <div class="mt-6 max-w-xl">
        @if (auth()->user()->two_factor_secret && auth()->user()->two_factor_confirmed_at)
            <!-- 2FA is Enabled -->
            <div class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
                        {{ __('Two-Factor Authentication is enabled.') }}
                    </h3>
                </div>
                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                    <p>
                        {{ __('Your account is protected with an extra layer of security.') }}
                    </p>
                    <div class="mt-4 flex space-x-3">
                        <x-secondary-button x-on:click.prevent="$dispatch('open-modal', 'view-recovery-codes')">
                            {{ __('View Recovery Codes') }}
                        </x-secondary-button>
                        <x-danger-button x-on:click.prevent="$dispatch('open-modal', 'confirm-disable-two-factor')">
                            {{ __('Disable Two-Factor') }}
                        </x-danger-button>
                    </div>
                </div>
            </div>
        @elseif (session('status') === 'two-factor-authentication-enabled' || !is_null(auth()->user()->two_factor_secret))
            <!-- 2FA Setup In Progress -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="ml-3 text-sm font-medium text-blue-800 dark:text-blue-200">
                        {{ __('Complete Two-Factor Authentication Setup') }}
                    </h3>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        {{ __('Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                    </p>

                    <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="flex flex-col items-center">
                            <!-- QR Code -->
                            <div class="mb-4 p-2 bg-white rounded">
                                {!! $qrCode ?? auth()->user()->twoFactorQrCodeSvg() !!}
                            </div>

                            <!-- Setup Key -->
                            <div class="w-full max-w-md">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ __('Or enter this code manually in your authenticator app:') }}
                                    </p>
                                    <button type="button" x-data="{ copied: false }"
                                        @click="
                                                const secret = '{{ $secret ?? decrypt(auth()->user()->two_factor_secret) }}';
                                                
                                                async function copyToClipboard(text) {
                                                    try {
                                                        // Try modern clipboard API first
                                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                                            await navigator.clipboard.writeText(text);
                                                            return true;
                                                        }
                                                        
                                                        // Fallback for older browsers
                                                        const textarea = document.createElement('textarea');
                                                        textarea.value = text;
                                                        textarea.style.position = 'fixed';
                                                        document.body.appendChild(textarea);
                                                        textarea.focus();
                                                        textarea.select();
                                                        
                                                        try {
                                                            return document.execCommand('copy');
                                                        } finally {
                                                            document.body.removeChild(textarea);
                                                        }
                                                    } catch (err) {
                                                        console.error('Failed to copy:', err);
                                                        window.Toast.fire({
                                                            icon: 'error',
                                                            title: '{{ __('Failed to copy to clipboard') }}'
                                                        });
                                                        return false;
                                                    }
                                                }
                                                
                                                copyToClipboard(secret).then(success => {
                                                    if (success) {
                                                        copied = true;
                                                        setTimeout(() => copied = false, 2000);
                                                    } else {
                                                        // Fallback: Show the text in an alert
                                                        alert('Failed to copy to clipboard. Here is the code to copy manually:\n\n' + secret);
                                                    }
                                                });
                                            "
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                        :title="copied ? 'Copied!' : 'Copy to clipboard'">
                                        <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                        <svg x-show="copied" xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>
                                <div
                                    class="bg-gray-100 dark:bg-gray-700 p-3 rounded font-mono text-center text-sm break-all">
                                    {{ $secret ?? decrypt(auth()->user()->two_factor_secret) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form method="POST" action="{{ route('profile.two-factor.confirm') }}" x-data="{
                            digits: ['', '', '', '', '', ''],
                            codeInputs: [],
                            hasError: {{ $errors->has('code') ? 'true' : 'false' }},
                            init() {
                                this.$nextTick(() => {
                                    this.codeInputs = Array.from(this.$el.querySelectorAll('.code-input'));
                                    this.codeInputs[0]?.focus();
                                });
                            },
                            handleInput(index, event) {
                                this.digits[index] = event.target.value.replace(/[^0-9]/g, '');
                                if (this.digits[index] && index < 5) {
                                    this.codeInputs[index + 1]?.focus();
                                }
                                this.$refs.codeInput.value = this.digits.join('');
                                if (this.digits.every(d => d !== '') && this.digits.length === 6) {
                                    this.$el.dispatchEvent(new Event('submit'));
                                }
                            },
                            handleKeyDown(index, event) {
                                if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                                    this.codeInputs[index - 1]?.focus();
                                }
                                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'v') {
                                    event.preventDefault();
                                    navigator.clipboard.readText().then(text => {
                                        const numbers = text.replace(/[^0-9]/g, '').split('').slice(0, 6);
                                        this.digits = [...numbers, ...Array(6 - numbers.length).fill('')].slice(0, 6);
                                        this.$refs.codeInput.value = this.digits.join('');
                                        const lastFilled = this.digits.findIndex(d => d === '') - 1;
                                        if (lastFilled >= 0 && lastFilled < 5) {
                                            this.codeInputs[lastFilled + 1]?.focus();
                                        } else if (this.digits.every(d => d !== '')) {
                                            this.$el.dispatchEvent(new Event('submit'));
                                        }
                                    });
                                }
                            }
                        }">
                            @csrf
                            <div>
                                <x-input-label :value="__('Verification Code')"
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300" />

                                <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                    <div class="flex justify-center gap-2 sm:gap-3 max-w-sm mx-auto">
                                        <template x-for="(digit, index) in 6" :key="index">
                                            <div class="flex-1 min-w-0">
                                                <input :id="'code-' + index" x-model="digits[index]"
                                                    @input="handleInput(index, $event)"
                                                    @keydown="handleKeyDown(index, $event)" type="text"
                                                    inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                                    class="w-full aspect-square text-center text-lg sm:text-xl font-medium bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all code-input"
                                                    :class="{ 'border-red-500 dark:border-red-500': hasError }"
                                                    :autofocus="index === 0"
                                                    style="-webkit-appearance: none; -moz-appearance: textfield;"
                                                    @input="hasError = false" />
                                            </div>
                                        </template>
                                    </div>
                                    <input type="hidden" name="code" x-ref="codeInput" />
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <x-primary-button type="submit" @click="$event.stopPropagation()">
                                    {{ __('Verify and Enable') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- 2FA is Disabled -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="ml-3 text-sm font-medium text-blue-800 dark:text-blue-200">
                        {{ __('Two-Factor Authentication is not enabled.') }}
                    </h3>
                </div>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>
                        {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s authenticator application.') }}
                    </p>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('profile.two-factor.enable') }}">
                            @csrf
                            <x-primary-button>
                                {{ __('Enable Two-Factor Authentication') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (session('status') === 'two-factor-authentication-disabled')
        <x-action-message type="success" message="{{ __('Two-factor authentication disabled successfully.') }}"
            class="mt-6" />
    @endif

    <!-- Confirm Disable Two Factor Modal -->
    <x-modal name="confirm-disable-two-factor" :show="$errors->disableTwoFactor->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.two-factor.disable') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Disable Two-Factor Authentication') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('For your security, please confirm your password to disable two-factor authentication.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="disable_two_factor_password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input id="disable_two_factor_password" name="password" type="password"
                    class="mt-1 block w-full" placeholder="{{ __('Password') }}" required />
                <x-input-error :messages="$errors->disableTwoFactor->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    {{ __('Disable Two-Factor') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <!-- View Recovery Codes Modal -->
    <x-modal name="view-recovery-codes" :show="false" focusable>
        <div x-data="recoveryCodes()" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Your Recovery Codes') }}
            </h2>

            <div>
                <template x-if="!showCodes">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Please confirm your password to view your recovery codes.') }}
                        </p>

                        <div class="mt-4">
                            <x-input-label for="recovery-codes-password" :value="__('Password')" />
                            <x-text-input id="recovery-codes-password" x-model="password" type="password"
                                class="mt-1 block w-full" placeholder="{{ __('Password') }}" x-ref="passwordInput"
                                @keyup.enter="verifyPassword" x-bind:disabled="isLoading" />
                            <p x-show="passwordError" x-text="passwordError"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button type="button" x-on:click="$dispatch('close')"
                                x-bind:disabled="isLoading">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-primary-button type="button" class="ml-3" @click="verifyPassword"
                                x-bind:disabled="isLoading">
                                <svg x-show="isLoading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-show="!isLoading">{{ __('View Codes') }}</span>
                                <span x-show="isLoading">{{ __('Verifying...') }}</span>
                            </x-primary-button>
                        </div>
                    </div>
                </template>

                <template x-if="showCodes">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Store these recovery codes in a secure password manager...') }}
                        </p>

                        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <template x-for="code in codes" :key="code">
                                    <div class="font-mono text-sm" x-text="code"></div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex space-x-2">
                                <button type="button" x-data="{ copied: false }"
                                    @click="
                                        copyCodes().then(() => {
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        });
                                    "
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                                    x-bind:disabled="isLoading" :title="copied ? 'Copied!' : 'Copy to clipboard'">
                                    <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-1 transition-colors duration-200" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                    <svg x-show="copied" xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-1 text-green-500 transition-colors duration-200"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                </button>
                                <button type="button" @click="downloadCodes"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                    x-bind:disabled="isLoading">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    {{ __('Download') }}
                                </button>
                            </div>
                            <button type="button" @click="regenerateCodes"
                                class="inline-flex items-center px-3 py-1.5 border border-amber-300 dark:border-amber-600 rounded-md text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-800/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 dark:focus:ring-offset-gray-800"
                                x-bind:disabled="isLoading">
                                <svg x-show="isLoading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-amber-500"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                {{ __('Regenerate Codes') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end">
                <template x-if="showCodes">
                    <x-primary-button type="button" x-on:click="$dispatch('close')">
                        {{ __('Close') }}
                    </x-primary-button>
                </template>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('recoveryCodes', () => ({
                    showCodes: false,
                    password: '',
                    passwordError: '',
                    isLoading: false, // ← DEFINED HERE
                    codes: [],

                    init() {
                        this.$watch('showCodes', value => {
                            if (value) {
                                this.$nextTick(() => this.$refs.passwordInput?.focus());
                            }
                        });
                        // Don't load recovery codes on init
                    },

                    async loadRecoveryCodes() {
                        try {
                            const response = await fetch(
                                '{{ route('profile.two-factor.recovery-codes') }}', {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    credentials: 'same-origin'
                                });

                            if (response.ok) {
                                const data = await response.json();
                                this.codes = data.recovery_codes || [];
                                return true;
                            } else if (response.status === 404) {
                                const data = await response.json();
                                if (data.needs_regeneration) {
                                    return this.regenerateCodes();
                                }
                            } else if (response.status === 401) {
                                // Session expired or not authenticated, will handle in verifyPassword
                                return false;
                            }
                            return false;
                        } catch (error) {
                            console.error('Error loading recovery codes:', error);
                            return false;
                        }
                    },

                    async verifyPassword() {
                        this.passwordError = '';
                        this.isLoading = true;

                        try {
                            const response = await fetch('{{ route('password.confirm') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    password: this.password
                                })
                            });

                            const data = await response.json();

                            if (response.ok) {
                                const loaded = await this.loadRecoveryCodes();
                                if (loaded) {
                                    this.showCodes = true;
                                } else if (!this.passwordError) {
                                    // Only show error if there wasn't already a password error
                                    this.passwordError =
                                        'Failed to load recovery codes. Please try again.';
                                    this.password = '';
                                }
                            } else {
                                this.passwordError = data?.errors?.password?.[0] ||
                                    'Incorrect password.';
                                this.password = '';
                                this.$refs.passwordInput?.focus();
                            }
                        } catch (error) {
                            this.passwordError = 'An error occurred.';
                            console.error(error);
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async regenerateCodes() {
                        const result = await Swal.fire({
                            title: '{{ __('Regenerate Recovery Codes') }}',
                            text: '{{ __('Are you sure you want to regenerate your recovery codes? This will invalidate your old recovery codes.') }}',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '{{ __('Yes, Regenerate') }}',
                            cancelButtonText: '{{ __('Cancel') }}',
                            reverseButtons: true
                        });

                        if (!result.isConfirmed) {
                            return false;
                        }

                        this.isLoading = true;

                        try {
                            // Ensure we have a password first
                            if (!this.password) {
                                this.passwordError = 'Please enter your password to continue';
                                this.$refs.passwordInput?.focus();
                                return false;
                            }

                            // First verify the password
                            await this.verifyPassword();

                            // If verification failed, stop here
                            if (!this.showCodes) {
                                throw new Error('Invalid password. Please try again.');
                            }

                            // Now proceed with regeneration
                            const response = await fetch(
                                '{{ route('profile.two-factor.regenerate-recovery-codes') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        _token: '{{ csrf_token() }}',
                                        password: this.password
                                    }),
                                    credentials: 'same-origin'
                                });

                            if (!response.ok) {
                                const errorData = await response.json();
                                throw new Error(errorData.message || errorData.error ||
                                    'Failed to regenerate codes');
                            }

                            const data = await response.json();
                            this.codes = data.recovery_codes || [];

                            // Show success message
                            if (typeof this.$dispatch === 'function') {
                                this.$dispatch('toast', {
                                    type: 'success',
                                    message: data.message ||
                                        'Recovery codes have been regenerated successfully.'
                                });
                            }

                            return true;
                        } catch (error) {
                            console.error('Error regenerating codes:', error);
                            if (typeof this.$dispatch === 'function') {
                                this.$dispatch('toast', {
                                    type: 'error',
                                    message: error.message ||
                                        'Failed to regenerate recovery codes. Please try again.'
                                });
                            }
                            return false;
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async copyCodes() {
                        try {
                            const text = this.codes.join('\n');

                            // Try using the modern clipboard API first
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                await navigator.clipboard.writeText(text);
                                return true;
                            }

                            // Fallback for older browsers
                            const textarea = document.createElement('textarea');
                            textarea.value = text;
                            textarea.style.position = 'fixed'; // Avoid scrolling to bottom
                            document.body.appendChild(textarea);
                            textarea.focus();
                            textarea.select();

                            try {
                                const successful = document.execCommand('copy');
                                if (!successful) {
                                    throw new Error('Copy command failed');
                                }
                                return true;
                            } finally {
                                document.body.removeChild(textarea);
                            }
                        } catch (err) {
                            console.error('Failed to copy:', err);
                            this.$dispatch('toast', {
                                type: 'error',
                                message: 'Failed to copy to clipboard. Please copy manually.'
                            });
                            throw err; // Re-throw to allow the button to handle the error state
                        }
                    },

                    downloadCodes() {
                        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                        @php
                            $appName = strtolower(preg_replace('/\s+/', '-', config('app.name')));
                        @endphp
                        const appName = '{{ $appName }}';
                        const blob = new Blob([this.codes.join('\n')], {
                            type: 'text/plain'
                        });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `${appName}-recovery-codes-${timestamp}.txt`;
                        a.click();
                        URL.revokeObjectURL(url);
                        this.$dispatch('toast', {
                            type: 'success',
                            message: 'Downloaded!'
                        });
                    }
                }));
            });
        </script>
    @endpush
</section>
