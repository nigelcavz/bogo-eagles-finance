<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-heading">
                Add Member
            </h2>
            <p class="section-subheading">Create a new member profile with clear, complete club records.</p>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-4xl">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-body text-gray-900">
                    <form method="POST" action="{{ route('members.store') }}">
                        @csrf

                        <div class="form-grid">
                            <div class="field-stack md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Login Email</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                                <p class="text-xs text-gray-500">
                                    A linked member login account will be created automatically using this email address.
                                </p>
                            </div>

                            <div class="field-stack">
                                <label for="member_code" class="block text-sm font-medium text-gray-700">Member Code</label>
                                <input
                                    id="member_code"
                                    name="member_code"
                                    type="text"
                                    value="{{ old('member_code') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="membership_status" class="block text-sm font-medium text-gray-700">Membership Status</label>
                                <select
                                    id="membership_status"
                                    name="membership_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                                    <option value="active" {{ old('membership_status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('membership_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="field-stack">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    value="{{ old('first_name') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                            </div>

                            <div class="field-stack">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old('last_name') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                            </div>

                            <div class="field-stack">
                                <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <input
                                    id="middle_name"
                                    name="middle_name"
                                    type="text"
                                    value="{{ old('middle_name') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix</label>
                                <input
                                    id="suffix"
                                    name="suffix"
                                    type="text"
                                    value="{{ old('suffix') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                <input
                                    id="gender"
                                    name="gender"
                                    type="text"
                                    value="{{ old('gender') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="birthdate" class="block text-sm font-medium text-gray-700">Birthdate</label>
                                <input
                                    id="birthdate"
                                    name="birthdate"
                                    type="date"
                                    value="{{ old('birthdate') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <input
                                    id="contact_number"
                                    name="contact_number"
                                    type="text"
                                    value="{{ old('contact_number') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="joined_at" class="block text-sm font-medium text-gray-700">Joined Date</label>
                                <input
                                    id="joined_at"
                                    name="joined_at"
                                    type="date"
                                    value="{{ old('joined_at') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea
                                    id="address"
                                    name="address"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >{{ old('address') }}</textarea>
                            </div>

                            <div class="field-stack md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5">
                            <x-primary-button>
                                Save Member
                            </x-primary-button>

                            <a
                                href="{{ route('members.index') }}"
                                class="btn-secondary"
                            >
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
