@extends('layouts.app')

@section('title', 'Kelola Vendor - D-WarungS')

@section('header')
    <h2 class="text-xl font-semibold text-gray-800">Kelola Vendor</h2>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Vendor</h1>
            <p class="mt-2 text-gray-600">Kelola dan tambah vendor baru</p>
        </div>
        <a href="{{ route('admin.admin.vendors.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out shadow-sm">
            Tambah Vendor
        </a>

    </div>


    @if($vendors->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">Belum ada vendor</h2>
            <p class="text-gray-500">Ketika vendor mendaftar, mereka akan muncul di sini.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemilik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($vendors as $vendor)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($vendor->logo)
                                            <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                                <span class="text-lg font-bold text-orange-600">{{ substr($vendor->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $vendor->name }}</p>
                                            @if($vendor->address)
                                                <p class="text-sm text-gray-500">{{ $vendor->address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $vendor->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $vendor->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @switch($vendor->status)
                                            @case('active') bg-green-100 text-green-800 @break
                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                            @case('suspended') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800 @endswitch">
                                        @switch($vendor->status)
                                            @case('active') Aktif @break
                                            @case('pending') Menunggu @break
                                            @case('suspended') Ditangguhkan @break
                                            @default {{ ucfirst($vendor->status) }}
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($vendor->rating)
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ number_format($vendor->rating, 1) }}</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">Belum ada rating</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $vendor->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($vendor->status === 'pending')
                                        <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-green-600 hover:text-green-900 mr-3">
                                                Setuju
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.vendors.reject', $vendor) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Tolak
                                            </button>
                                        </form>
                                    @elseif($vendor->status === 'active')
                                        <form action="{{ route('admin.vendors.reject', $vendor) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menangguhkan vendor ini?')">
                                                Tangguhkan
                                            </button>
                                        </form>
                                    @elseif($vendor->status === 'suspended')
                                        <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                Aktifkan Kembali
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

