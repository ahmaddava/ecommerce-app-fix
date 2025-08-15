@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Pesanan Menunggu Verifikasi Pembayaran</h3>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Semua Pesanan
                    </a>
                </div>
                
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Tanggal Upload</th>
                                        <th>Bukti Transfer</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <strong>{{ $order->order_number }}</strong>
                                        </td>
                                        <td>
                                            {{ $order->customer_name }}<br>
                                            <small class="text-muted">{{ $order->customer_email }}</small>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ strtoupper($order->payment_method) }}</span>
                                        </td>
                                        <td>
                                            {{ $order->updated_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            @if($order->payment_proof)
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-toggle="modal" data-target="#proofModal{{ $order->id }}">
                                                    <i class="fas fa-eye"></i> Lihat Bukti
                                                </button>
                                            @else
                                                <span class="text-muted">Tidak ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        data-toggle="modal" data-target="#verifyModal{{ $order->id }}" 
                                                        data-action="approve">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-toggle="modal" data-target="#verifyModal{{ $order->id }}" 
                                                        data-action="reject">
                                                    <i class="fas fa-times"></i> Tolak
                                                </button>
                                                <a href="{{ route('admin.orders.show', $order->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Payment Proof Modal -->
                                    @if($order->payment_proof)
                                    <div class="modal fade" id="proofModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Bukti Transfer - {{ $order->order_number }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="{{ asset('storage/' . $order->payment_proof) }}" 
                                                         alt="Bukti Transfer" 
                                                         class="img-fluid" 
                                                         style="max-height: 500px;">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                    <a href="{{ asset('storage/' . $order->payment_proof) }}" 
                                                       target="_blank" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Verification Modal -->
                                    <div class="modal fade" id="verifyModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.orders.verify.payment', $order->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Verifikasi Pembayaran</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" id="action{{ $order->id }}">
                                                        
                                                        <div class="alert alert-info">
                                                            <strong>Pesanan:</strong> {{ $order->order_number }}<br>
                                                            <strong>Customer:</strong> {{ $order->customer_name }}<br>
                                                            <strong>Total:</strong> Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="admin_notes{{ $order->id }}">Catatan Admin (Opsional)</label>
                                                            <textarea name="admin_notes" 
                                                                      id="admin_notes{{ $order->id }}" 
                                                                      class="form-control" 
                                                                      rows="3" 
                                                                      placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary" id="submitBtn{{ $order->id }}">
                                                            Konfirmasi
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada pesanan yang menunggu verifikasi</h5>
                            <p class="text-muted">Semua pembayaran sudah diverifikasi atau belum ada yang mengupload bukti transfer.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle verification modal
    document.querySelectorAll('[data-toggle="modal"][data-action]').forEach(function(button) {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const orderId = this.getAttribute('data-target').replace('#verifyModal', '');
            const actionInput = document.getElementById('action' + orderId);
            const submitBtn = document.getElementById('submitBtn' + orderId);
            
            actionInput.value = action;
            
            if (action === 'approve') {
                submitBtn.textContent = 'Setujui Pembayaran';
                submitBtn.className = 'btn btn-success';
            } else {
                submitBtn.textContent = 'Tolak Pembayaran';
                submitBtn.className = 'btn btn-danger';
            }
        });
    });
});
</script>
@endsection

