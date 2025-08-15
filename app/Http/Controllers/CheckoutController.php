<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BankAccount;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::where("user_id", $user->id)->with("product")->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route("cart.index")->with("error", "Keranjang belanja Anda kosong.");
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        // For simplicity, assume shipping is fixed for now
        $shippingCost = 25000;
        $totalAmount = $subtotal + $shippingCost;

        // Get active bank accounts for manual payment
        $bankAccounts = BankAccount::active()->get();

        return view("checkout.index", compact("cartItems", "subtotal", "shippingCost", "totalAmount", "bankAccounts"));
    }

    public function process(Request $request)
    {
        // Validasi input, termasuk bukti pembayaran yang wajib jika metode transfer dipilih
        $request->validate([
            "address" => "required|string|max:255",
            "city" => "required|string|max:255",
            "postal_code" => "required|string|max:10",
            "phone_number" => "required|string|max:20",
            "payment_method" => "required|string|in:bni,bca,qris",
            // Jadikan 'payment_proof' wajib jika metode pembayaran adalah bni atau bca
            "payment_proof" => "required_if:payment_method,bni,bca|image|mimes:jpeg,png,jpg|max:2048",
        ], [
            'payment_proof.required_if' => 'Bukti pembayaran wajib diunggah untuk metode transfer bank.',
            'payment_proof.image' => 'File yang diunggah harus berupa gambar.',
            'payment_proof.mimes' => 'Format gambar yang didukung adalah jpeg, png, dan jpg.',
            'payment_proof.max' => 'Ukuran gambar maksimal adalah 2MB.',
        ]);

        $user = Auth::user();
        $cartItems = Cart::where("user_id", $user->id)->with("product")->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route("cart.index")->with("error", "Keranjang belanja Anda kosong.");
        }

        DB::beginTransaction();
        try {
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $shippingCost = 25000;
            $totalAmount = $subtotal + $shippingCost;

            // Tentukan status pembayaran awal
            $paymentStatus = 'pending';
            if (in_array($request->payment_method, ['bni', 'bca'])) {
                $paymentStatus = 'pending_verification'; // Status baru untuk menunggu verifikasi admin
            }

            $order = Order::create([
                "user_id" => $user->id,
                "order_number" => "ORD-" . time() . rand(100, 999),
                "customer_name" => $user->name,
                "customer_email" => $user->email,
                "total_amount" => $totalAmount,
                "shipping_cost" => $shippingCost,
                "status" => "pending",
                "payment_status" => $paymentStatus,
                "payment_method" => $request->payment_method,
                "shipping_address" => $request->address . ", " . $request->city . ", " . $request->postal_code,
                "customer_phone" => $request->phone_number,
            ]);

            // Handle upload bukti pembayaran jika ada
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('payment_proofs', 'public');
                $order->payment_proof = $path;
                $order->save();
            }

            foreach ($cartItems as $item) {
                OrderItem::create([
                    "order_id" => $order->id,
                    "product_id" => $item->product_id,
                    "quantity" => $item->quantity,
                    "price" => $item->product->price,
                ]);

                // Update product stock
                $product = $item->product;
                $product->stock -= $item->quantity;
                $product->save();
            }

            // Clear cart
            Cart::where("user_id", $user->id)->delete();

            // Jika menggunakan QRIS (Midtrans), buat transaksi
            if ($request->payment_method === 'qris') {
                $midtransService = new MidtransService();
                $snapToken = $midtransService->createTransaction($order);

                // Simpan snap token ke order
                $order->payment_reference = $snapToken;
                $order->save();
            }

            DB::commit();

            // Arahkan semua ke halaman sukses
            return redirect()->route("checkout.success", $order->id)->with("success", "Pesanan Anda berhasil dibuat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with("error", "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage())->withInput();
        }
    }

    public function success(Order $order)
    {
        // Pastikan hanya pemilik order yang bisa melihat halaman sukses
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view("checkout.success", compact("order"));
    }

    // Metode di bawah ini tetap ada, bisa digunakan untuk fitur lain seperti
    // mengunggah ulang bukti pembayaran dari halaman riwayat pesanan.

    public function showPayment(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $bankAccount = BankAccount::where('bank_name', strtoupper($order->payment_method))
            ->where('is_active', true)
            ->first();

        if (!$bankAccount && in_array($order->payment_method, ['bni', 'bca'])) {
            return redirect()->route('customer.orders.index')
                ->with('error', 'Bank account not found for this payment method.');
        }

        return view("checkout.payment", compact("order", "bankAccount"));
    }

    public function uploadPaymentProof(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            $path = $request->file('payment_proof')->store('payment_proofs', 'public');

            $order->update([
                'payment_proof' => $path,
                'payment_status' => 'pending_verification'
            ]);

            return redirect()->route('payment.show', $order->id)
                ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload bukti pembayaran: ' . $e->getMessage());
        }
    }

    public function notification(Request $request)
    {
        try {
            $midtransService = new MidtransService();
            $midtransService->handleNotification($request);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification handled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        return response()->json(["message" => "Callback received"], 200);
    }
}
