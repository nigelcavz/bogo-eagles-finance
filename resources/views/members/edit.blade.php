<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-heading">
                Edit Member
            </h2>
            <p class="section-subheading">Update member details while keeping the record clear and audit-friendly.</p>
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
                    <form method="POST" action="{{ route('members.update', $member) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="field-stack">
                                <label for="member_code" class="block text-sm font-medium text-gray-700">Member Code</label>
                                <input
                                    id="member_code"
                                    name="member_code"
                                    type="text"
                                    value="{{ old('member_code', $member->member_code) }}"
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
                                    <option value="active" {{ old('membership_status', $member->membership_status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('membership_status', $member->membership_status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="field-stack">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    value="{{ old('first_name', $member->first_name) }}"
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
                                    value="{{ old('last_name', $member->last_name) }}"
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
                                    value="{{ old('middle_name', $member->middle_name) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix</label>
                                <input
                                    id="suffix"
                                    name="suffix"
                                    type="text"
                                    value="{{ old('suffix', $member->suffix) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                <input
                                    id="gender"
                                    name="gender"
                                    type="text"
                                    value="{{ old('gender', $member->gender) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="birthdate" class="block text-sm font-medium text-gray-700">Birthdate</label>
                                <input
                                    id="birthdate"
                                    name="birthdate"
                                    type="date"
                                    value="{{ old('birthdate', $member->birthdate?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <input
                                    id="contact_number"
                                    name="contact_number"
                                    type="text"
                                    value="{{ old('contact_number', $member->contact_number) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                            </div>

                            <div class="field-stack">
                                <label for="joined_at" class="block text-sm font-medium text-gray-700">Joined Date</label>
                                <input
                                    id="joined_at"
                                    name="joined_at"
                                    type="date"
                                    value="{{ old('joined_at', $member->joined_at?->format('Y-m-d')) }}"
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
                                >{{ old('address', $member->address) }}</textarea>
                            </div>

                            <div class="field-stack md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >{{ old('notes', $member->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5">
                            <x-primary-button>
                                Update Member
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
