@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Keranjang</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>

        <h1 class="mb-4 text-gradient"><i class="bi bi-credit-card"></i> Checkout</h1>

        <div class="row">
            <div class="col-md-8">
                <form action="{{ route('checkout.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Informasi Pengiriman</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat Lengkap <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                    required>{{ old('address', Auth::user()->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Kota <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                        id="city" name="city" value="{{ old('city', Auth::user()->city) }}"
                                        required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Kode Pos <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                        id="postal_code" name="postal_code"
                                        value="{{ old('postal_code', Auth::user()->postal_code) }}" required>
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Nomor Telepon <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                    id="phone_number" name="phone_number"
                                    value="{{ old('phone_number', Auth::user()->phone_number) }}" required>
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-wallet2"></i> Metode Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method"
                                    id="paymentBNI" value="bni"
                                    {{ old('payment_method') == 'bni' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="paymentBNI">
                                    <img src="{{ asset('images/bni.png') }}" alt="BNI" height="25"
                                        class="me-2"> Bank Transfer BNI
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method"
                                    id="paymentBCA" value="bca"
                                    {{ old('payment_method') == 'bca' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="paymentBCA">
                                    <img src="{{ asset('images/bca.png') }}" alt="BCA" height="25"
                                        class="me-2"> Bank Transfer BCA
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input payment-method-radio" type="radio" name="payment_method"
                                    id="paymentQRIS" value="qris"
                                    {{ old('payment_method') == 'qris' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="paymentQRIS">
                                    <img src="{{ asset('images/qris.png') }}" alt="QRIS" height="25"
                                        class="me-2"> QRIS
                                </label>
                            </div>
                            
                            @error('payment_method')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror

                            <div id="bni-details" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Silakan Transfer ke Rekening BNI:</h6>
                                    <p class="mb-0"><strong>Nomor Rekening:</strong> 1234567890</p>
                                    <p class="mb-0"><strong>Atas Nama:</strong> PT. Unik Koleksi Indonesia</p>
                                </div>
                            </div>
                            
                            <div id="bca-details" class="mt-3" style="display: none;">
                                <div class="alert alert-primary">
                                    <h6 class="alert-heading">Silakan Transfer ke Rekening BCA:</h6>
                                    <p class="mb-0"><strong>Nomor Rekening:</strong> 0987654321</p>
                                    <p class="mb-0"><strong>Atas Nama:</strong> PT. Unik Koleksi Indonesia</p>
                                </div>
                            </div>

                            <div id="payment-proof-upload" class="mt-3" style="display: none;">
                                <label for="payment_proof" class="form-label fw-bold">Upload Bukti Pembayaran <span class="text-danger">*</span></label>
                                <input class="form-control @error('payment_proof') is-invalid @enderror" type="file" id="payment_proof" name="payment_proof" accept="image/*">
                                @error('payment_proof')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100"><i
                            class="bi bi-check-circle"></i> Proses Pesanan</button>
                </form>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-orange text-white">
                        <h5 class="mb-0"><i class="bi bi-cart"></i> Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3">
                            @foreach ($cartItems as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="my-0">{{ $item->product->name }}</h6>
                                        <small class="text-muted">Jumlah: {{ $item->quantity }}</small>
                                    </div>
                                    <span class="text-muted">Rp
                                        {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Subtotal
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Biaya Pengiriman
                                <span>Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center fw-bold text-gradient-dark">
                                Total Pembayaran
                                <h4>Rp {{ number_format($totalAmount, 0, ',', '.') }}</h4>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil semua elemen yang kita butuhkan
        const paymentRadios = document.querySelectorAll('.payment-method-radio');
        const bniDetails = document.getElementById('bni-details');
        const bcaDetails = document.getElementById('bca-details');
        const uploadContainer = document.getElementById('payment-proof-upload');
        const fileInput = document.getElementById('payment_proof');

        // Fungsi untuk menampilkan/menyembunyikan elemen berdasarkan pilihan
        function togglePaymentDetails() {
            const selectedValue = document.querySelector('input[name="payment_method"]:checked')?.value;

            // Sembunyikan semua detail terlebih dahulu
            bniDetails.style.display = 'none';
            bcaDetails.style.display = 'none';
            uploadContainer.style.display = 'none';
            fileInput.required = false; // Jadikan tidak wajib

            if (selectedValue === 'bni') {
                bniDetails.style.display = 'block';
                uploadContainer.style.display = 'block';
                fileInput.required = true; // Wajibkan upload bukti
            } else if (selectedValue === 'bca') {
                bcaDetails.style.display = 'block';
                uploadContainer.style.display = 'block';
                fileInput.required = true; // Wajibkan upload bukti
            }
        }

        // Tambahkan event listener untuk setiap radio button
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', togglePaymentDetails);
        });

        // Panggil fungsi saat halaman pertama kali dimuat, untuk menangani jika ada 'old' value
        togglePaymentDetails();
    });
</script>
@endpush