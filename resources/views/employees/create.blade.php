<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-stone-900">
                Add Employee
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Create an account and employee profile for operational staff.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            <x-app-card>

                <form
                    method="POST"
                    action="{{ route('employees.store') }}"
                    class="space-y-6"
                >
                    @csrf

                    {{-- ========================================= --}}
                    {{-- ACCOUNT INFORMATION --}}
                    {{-- ========================================= --}}

                    <div>
                        <h3 class="text-lg font-semibold text-stone-900">
                            Account Information
                        </h3>

                        <p class="mt-1 text-sm text-stone-500">
                            Information used by the employee to log in to the system.
                        </p>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="e.g. Budi Santoso"
                            required
                            autofocus
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >

                        @error('name')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="e.g. budi@kopikeun.test"
                            required
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >

                        @error('email')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >

                        @error('password')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >
                    </div>

                    {{-- Role --}}
                    <div>
                        <label
                            for="role_id"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Role
                        </label>

                        <select
                            name="role_id"
                            id="role_id"
                            required
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >
                            <option value="">
                                Select Role
                            </option>

                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->id_role }}"
                                    {{ old('role_id') == $role->id_role ? 'selected' : '' }}
                                >
                                    {{ $role->nama_role }}
                                </option>
                            @endforeach
                        </select>

                        @error('role_id')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- ========================================= --}}
                    {{-- EMPLOYEE INFORMATION --}}
                    {{-- ========================================= --}}

                    <div class="border-t border-stone-200 pt-6">

                        <h3 class="text-lg font-semibold text-stone-900">
                            Employee Information
                        </h3>

                        <p class="mt-1 text-sm text-stone-500">
                            Information related to the employee profile.
                        </p>

                    </div>
                                        
                    {{-- Employee Code --}}
                    <div>
                        <label
                            for="employee_code"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Employee Code
                        </label>

                        <input
                            type="text"
                            id="employee_code"
                            value="Generated automatically"
                            readonly
                            class="mt-1 block w-full rounded-lg border-stone-300
                                bg-stone-100 text-stone-500 shadow-sm
                                cursor-not-allowed"
                        >

                        <p class="mt-1 text-xs text-stone-500">
                            Employee code will be generated automatically
                            when the employee is saved.
                            Example: KK-EMP-003.
                        </p>
                    </div>


                    {{-- Phone --}}
                    <div>
                        <label
                            for="phone"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone') }}"
                            placeholder="e.g. 081234567890"
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >

                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Address --}}
                    <div>
                        <label
                            for="address"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Address
                        </label>

                        <textarea
                            name="address"
                            id="address"
                            rows="3"
                            placeholder="Enter employee address"
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >{{ old('address') }}</textarea>

                        @error('address')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-stone-700"
                        >
                            Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            required
                            class="mt-1 block w-full rounded-lg border-stone-300
                                   shadow-sm focus:border-amber-600
                                   focus:ring-amber-600"
                        >
                            <option
                                value="Active"
                                {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}
                            >
                                Active
                            </option>

                            <option
                                value="Inactive"
                                {{ old('status') === 'Inactive' ? 'selected' : '' }}
                            >
                                Inactive
                            </option>
                        </select>

                        @error('status')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- ========================================= --}}
                    {{-- BUTTONS --}}
                    {{-- ========================================= --}}

                    <div class="flex flex-col gap-2 border-t border-stone-200 pt-6 sm:flex-row">

                        <button
                            type="submit"
                            class="inline-flex justify-center rounded-lg
                                   bg-amber-700 px-4 py-2 text-sm
                                   font-semibold text-white
                                   hover:bg-amber-800"
                        >
                            Save Employee
                        </button>

                        <a
                            href="{{ route('employees.index') }}"
                            class="inline-flex justify-center rounded-lg
                                   bg-stone-200 px-4 py-2 text-sm
                                   font-semibold text-stone-700
                                   hover:bg-stone-300"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </x-app-card>

        </div>
    </div>
</x-app-layout>
