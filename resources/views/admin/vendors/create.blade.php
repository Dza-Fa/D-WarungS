@extends('layouts.app')

@section('title', 'Tambah Vendor - D-WarungS')

@section('header')
    <h2 class="text-xl font-semibold text-gray-800">Tambah Vendor Baru</h2>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-8">
            <form action="{{ route('admin.admin.vendors.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Nama Vendor')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email Pemilik')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <x-input-label for="phone" :value="__('No Telepon')" />
                        <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <!-- Address -->
                <div class="mt-6">
                    <x-input-label for="address" :value="__('Alamat')" />
                    <x-textarea id="address" name="address" rows="3" class="mt-1 block w-full" :value="old('address')" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <x-input-label for="description" :value="__('Deskripsi')" />
                    <x-textarea id="description" name="description" rows="4" class="mt-1 block w-full" :value="old('description')" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <!-- Status -->
                <div class="mt-6">
                    <x-input-label for="status" :value="__('Status Awal')" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending (Review)</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-8">
                    <x-primary-button>
                        {{ __('Tambah Vendor') }}
                    </x-primary-button>
                    <a href="{{ route('admin.vendors.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

