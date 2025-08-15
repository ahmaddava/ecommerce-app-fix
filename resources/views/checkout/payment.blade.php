@extends('layouts.app')

@section('title', 'Pembayaran - ' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Order #{{ $order->order_number }}</h1>
            <p class="text-gray-600">Silakan lakukan pembayaran sesuai dengan instruksi di bawah ini</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payment Instructions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Instruksi Pembayaran</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-semibold text-blue-800 mb-2">Transfer ke Rekening {{ strtoupper($bankAccount->bank_name) }}</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank:</span>
                            <span class="font-medium">{{ $bankAccount->bank_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">No. Rekening:</span>
                            <span class="font-medium font-mono">{{ $bankAccount->account_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Atas Nama:</span>
                            <span class="font-medium">{{ $bankAccount->account_holder }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-gray-600">Jumlah Transfer:</span>
                            <span class="font-bold text-lg text-green-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-800 mb-2">Penting!</h4>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li>• Transfer sesuai dengan jumlah yang tertera</li>
                        <li>• Simpan bukti transfer Anda</li>
                        <li>• Upload bukti transfer di form sebelah kanan</li>
                        <li>• Pembayaran akan diverifikasi dalam 1x24 jam</li>
                    </ul>
                </div>
            </div>

            <!-- Upload Payment Proof -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Bukti Transfer</h2>
                
                @if($order->payment_proof)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-green-800 mb-2">Bukti Transfer Sudah Diupload</h4>
                        <p class="text-sm text-green-700 mb-3">Status: 
                            @if($order->payment_status === 'pending_verification')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Menunggu Verifikasi</span>
                            @elseif($order->payment_status === 'paid')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Terverifikasi</span>
                            @elseif($order->payment_status === 'failed')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Ditolak</span>
                            @endif
                        </p>
                        <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Bukti Transfer" class="max-w-full h-auto rounded border">
                        
                        @if($order->payment_status !== 'paid')
                            <p class="text-sm text-green-700 mt-2">Anda dapat mengupload ulang bukti transfer jika diperlukan.</p>
                        @endif
                    </div>
                @endif

                @if($order->payment_status !== 'paid')
                    <form action="{{ route('payment.upload', $order->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih File Bukti Transfer
                            </label>
                            <input type="file" 
                                   id="payment_proof" 
                                   name="payment_proof" 
                                   accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                   required>
                            @error('payment_proof')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="text-xs text-gray-500 mb-4">
                            Format yang didukung: JPG, JPEG, PNG (Maksimal 2MB)
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                            Upload Bukti Transfer
                        </button>
                    </form>
                @else
                    <div class="text-center py-4">
                        <div class="text-green-600 text-lg font-semibold">✓ Pembayaran Terverifikasi</div>
                        <p class="text-gray-600 text-sm mt-2">Pesanan Anda sedang diproses</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Informasi Pengiriman</h4>
                    <p class="text-sm text-gray-600">{{ $order->customer_name }}</p>
                    <p class="text-sm text-gray-600">{{ $order->customer_phone }}</p>
                    <p class="text-sm text-gray-600">{{ $order->shipping_address }}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Detail Pembayaran</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($order->total_amount - $order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Ongkir:</span>
                            <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-semibold border-t pt-1">
                            <span>Total:</span>
                            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Orders -->
        <div class="text-center mt-6">
            <a href="{{ route('customer.orders.index') }}" 
               class="inline-block bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-200">
                Kembali ke Daftar Pesanan
            </a>
        </div>
    </div>
</div>
@endsection

