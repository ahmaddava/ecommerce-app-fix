<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with('user');
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Count orders pending verification
        $pendingVerificationCount = Order::where('payment_status', 'pending_verification')->count();
        
        return view('admin.orders.index', compact('orders', 'pendingVerificationCount'));
    }

    /**
     * Display orders pending payment verification.
     */
    public function pendingVerification()
    {
        $orders = Order::with(['user', 'orderItems.product'])
                      ->where('payment_status', 'pending_verification')
                      ->whereNotNull('payment_proof')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return view('admin.orders.pending-verification', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'orderItems.product', 'verifier'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        
        // Update timestamps based on status
        if ($request->status === 'shipped' && !$order->shipped_at) {
            $order->update(['shipped_at' => now()]);
        } elseif ($request->status === 'delivered' && !$order->delivered_at) {
            $order->update(['delivered_at' => now()]);
        }

        return back()->with('success', 'Status pesanan berhasil diupdate');
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->payment_status]);

        return back()->with('success', 'Status pembayaran berhasil diupdate');
    }

    /**
     * Verify payment proof.
     */
    public function verifyPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $order = Order::findOrFail($id);
        
        if ($request->action === 'approve') {
            $order->update([
                'payment_status' => 'paid',
                'payment_verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => $request->admin_notes,
                'status' => 'processing' // Automatically move to processing when payment is verified
            ]);
            
            $message = 'Pembayaran berhasil diverifikasi dan pesanan akan diproses';
        } else {
            $order->update([
                'payment_status' => 'failed',
                'payment_verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => $request->admin_notes ?: 'Bukti pembayaran tidak valid'
            ]);
            
            $message = 'Pembayaran ditolak';
        }

        return back()->with('success', $message);
    }

    /**
     * Print order invoice.
     */
    public function invoice($id)
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);
        return view('admin.orders.invoice', compact('order'));
    }
}
