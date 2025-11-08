<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __("Browser Sessions") }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 font-semibold">
            {{
                __(
                    "Manage and log out your active sessions on other browsers and devices."
                )
            }}
        </p>

        <p class="mt-1 text-sm text-gray-600">
            {{
                __(
                    "If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password."
                )
            }}
        </p>
    </header>

    <div class="mt-5 max-w-xl">
        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
        >
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if (count($sessions) > 0)
                <div class="space-y-6">
                    @foreach ($sessions as $session)
                    <div class="flex items-center">
                        <div>
                            @php
                                $isDesktop = is_object($session->user_agent) && method_exists($session->user_agent, 'isDesktop') ? $session->user_agent->isDesktop() : false;
                            @endphp
                            @if ($isDesktop)
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-8 h-8 text-gray-500"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"
                                />
                            </svg>
                            @else
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-8 h-8 text-gray-500"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"
                                />
                            </svg>
                            @endif
                        </div>

                        <div class="ml-3">
                            <div
                                class="text-sm text-gray-600 dark:text-gray-300"
                            >
                                @php
                                    $platform = is_object($session->user_agent) && method_exists($session->user_agent, 'platform') ? $session->user_agent->platform() : 'Unknown';
                                    $browser = is_object($session->user_agent) && method_exists($session->user_agent, 'browser') ? $session->user_agent->browser() : 'Unknown';
                                    echo "{$platform} - {$browser}";
                                @endphp
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">
                                    {{ $session->ip_address }}, 
                                    @if($session->is_current_device)
                                        <span class="text-green-500 font-semibold">{{ __("This device") }}</span>
                                    @else
                                        {{ __("Last active") }} {{ $session->last_active }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-browser-sessions-deletion')"
        >{{ __("Log Out Other Browser Sessions") }}</x-danger-button
    >

    <!-- Log Out Other Devices Confirmation Modal -->
    <x-modal
        name="confirm-browser-sessions-deletion"
        :show="$errors->browserSessionsDeletion->isNotEmpty()"
        focusable
    >
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{
                    __(
                        "Are you sure you want to log out of other browser sessions?"
                    )
                }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{
                    __(
                        "Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices."
                    )
                }}
            </p>

            <div class="mt-6">
                <x-input-label
                    for="browser_sessions_password"
                    value="{{ __('Password') }}"
                    class="sr-only"
                />

                <x-text-input
                    id="browser_sessions_password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                    required
                />

                <x-input-error
                    :messages="$errors->browserSessionsDeletion->get('password')"
                    class="mt-2"
                />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __("Cancel") }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __("Log Out Other Browser Sessions") }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
