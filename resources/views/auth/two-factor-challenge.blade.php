<x-guest-layout>
    <!-- Two Factor Authentication -->
    <div x-data="{ recovery: false }">
        <div class="mb-4 text-sm text-gray-600" x-show="! recovery">
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        </div>

        <div class="mb-4 text-sm text-gray-600" x-show="recovery">
            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
        </div>

        <form method="POST" action="{{ route('two-factor.login') }}" x-show="! recovery" x-data="{
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
                // Only allow numbers
                this.digits[index] = event.target.value.replace(/[^0-9]/g, '');
                
                // Move to next input if current input has a value
                if (this.digits[index] !== '' && index < 5) {
                    this.codeInputs[index + 1]?.focus();
                }
                
                // Update hidden input with full code
                this.$refs.codeInput.value = this.digits.join('');
                
                // Auto-submit if all digits are filled
                if (this.digits.every(d => d !== '') && this.digits.length === 6) {
                    this.$el.dispatchEvent(new Event('submit'));
                }
            },
            handleKeyDown(index, event) {
                // Handle backspace
                if (event.key === 'Backspace' && this.digits[index] === '' && index > 0) {
                    this.codeInputs[index - 1]?.focus();
                }
                
                // Handle paste
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'v') {
                    event.preventDefault();
                    navigator.clipboard.readText().then(text => {
                        const numbers = text.replace(/[^0-9]/g, '').split('').slice(0, 6);
                        this.digits = [...numbers, ...Array(6 - numbers.length).fill('')].slice(0, 6);
                        this.$refs.codeInput.value = this.digits.join('');
                        const lastFilledIndex = this.digits.findIndex(d => d === '') - 1;
                        if (lastFilledIndex >= 0 && lastFilledIndex < 5) {
                            this.codeInputs[lastFilledIndex + 1]?.focus();
                        } else if (this.digits.every(d => d !== '')) {
                            this.$el.dispatchEvent(new Event('submit'));
                        }
                    });
                }
            }
        }">
            @csrf

            <div class="mt-4">
                <x-input-label :value="__('Verification Code')" class="text-sm font-medium text-gray-700 dark:text-gray-300" />

                <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="flex justify-center gap-2 sm:gap-3 max-w-sm mx-auto">
                        <template x-for="(digit, index) in 6" :key="index">
                            <div class="flex-1 min-w-0">
                                <input
                                    :id="'code-' + index"
                                    x-model="digits[index]"
                                    @input="handleInput(index, $event)"
                                    @keydown="handleKeyDown(index, $event)"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    class="w-full aspect-square text-center text-lg sm:text-xl font-medium bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all code-input"
                                    :class="{ 'border-red-500 dark:border-red-500': hasError }"
                                    :autofocus="index === 0"
                                    style="-webkit-appearance: none; -moz-appearance: textfield;"
                                    @input="hasError = false"
                                />
                            </div>
                        </template>
                    </div>
                    <input type="hidden" name="code" x-ref="codeInput" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                x-on:click="
                                    recovery = true;
                                    $nextTick(() => $refs.recovery_code.focus())
                                ">
                    {{ __('Use a recovery code') }}
                </button>

                <x-primary-button class="ml-4">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('two-factor.login') }}" x-show="recovery">
            @csrf

            <div class="mt-4">
                <x-input-label for="recovery_code" value="{{ __('Recovery Code') }}" />
                <x-text-input
                    id="recovery_code"
                    type="text"
                    name="recovery_code"
                    class="block mt-1 w-full"
                    x-ref="recovery_code"
                    autocomplete="one-time-code"
                />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                x-on:click="
                                    recovery = false;
                                    $nextTick(() => $refs.code.focus())
                                ">
                    {{ __('Use an authentication code') }}
                </button>

                <x-primary-button class="ml-4">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            // Focus the code input when the page loads
            if (document.getElementById('code')) {
                document.getElementById('code').focus();
            }
        });
    </script>
@endpush