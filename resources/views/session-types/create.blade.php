<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-6 text-2xl font-bold text-slate-900">{{ __('app.session_type_management.create_title') }}</h2>

                <form action="{{ route('session-types.store') }}" method="POST">
                    @csrf

                    <x-input name="name" :label="__('app.session_type_management.fields.name')" type="text" />

                    <div class="mt-4">
                        <x-select name="duration_minutes" :label="__('app.session_type_management.fields.duration_minutes')" :options="__('app.session_type_management.duration_options')" />
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-input name="price" :label="__('app.session_type_management.fields.price')" type="number" step="0.01" />
                        <x-select name="currency" :label="__('app.session_type_management.fields.currency')" :options="__('app.session_type_management.currency_options')" />
                    </div>

                    <div class="mt-6 flex items-center">
                        <!-- Alpine.js Toggle para is_active -->
                        <div x-data="{ active: true }" class="flex items-center">
                            <button type="button" @click="active = !active"
                                    :class="active ? 'bg-umbralia-title' : 'bg-slate-200'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2">
                                <span :class="active ? 'translate-x-5' : 'translate-x-0'"
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span class="ml-3 text-sm text-slate-600" x-text="active ? @js(__('app.active')) : @js(__('app.inactive'))"></span>
                            <input type="hidden" name="is_active" :value="active ? 1 : 0">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('session-types.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('app.session_type_management.cancel') }}</a>
                        <button type="submit" class="rounded-lg bg-umbralia-title px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2">{{ __('app.session_type_management.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
