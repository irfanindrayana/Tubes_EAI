<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show payment form.
     */
    public function create(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment) {
            return redirect()->route('payment.status', $booking->payment)
                ->with('info', 'Payment already exists for this booking.');
        }

        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        return view('payment.create', compact('booking', 'paymentMethods'));
    }

    /**
     * Process payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment) {
            return back()->with('error', 'Payment already exists for this booking.');
        }

        $paymentProofPath = null;
        
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $fileName = 'payment_proof_' . $booking->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $paymentProofPath = $file->storeAs('payment_proofs', $fileName, 'public');
        }

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        
        $payment = Payment::create([
            'payment_code' => 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'user_id' => Auth::id(),
            'booking_id' => $booking->id,
            'payment_method' => $paymentMethod->code,
            'amount' => $booking->total_amount,
            'status' => 'pending',
            'proof_image' => $paymentProofPath,
        ]);

        return redirect()->route('payment.status', $payment)
            ->with('success', 'Payment submitted successfully! Please wait for verification.');
    }

    /**
     * Show payment status.
     */
    public function status(Payment $payment)
    {
        if ($payment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $payment->load(['booking.schedule.route', 'paymentMethod', 'verifiedBy']);
        
        return view('payment.status', compact('payment'));
    }

    /**
     * Show user's payments.
     */
    public function myPayments()
    {
        $payments = Payment::with(['booking.schedule.route', 'paymentMethod'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('payment.my-payments', compact('payments'));
    }

    /**
     * Admin: Show pending payments for verification.
     */
    public function pendingPayments()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $payments = Payment::with(['user', 'booking.schedule.route', 'paymentMethod'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('admin.payments.pending', compact('payments'));
    }

    /**
     * Admin: Verify payment.
     */
    public function verify(Request $request, Payment $payment)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status' => $request->status,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_notes' => $request->notes,
        ]);

        // Update booking status
        if ($request->status === 'verified') {
            $payment->booking->update(['status' => 'confirmed']);
        } elseif ($request->status === 'rejected') {
            // Make seats available again
            if (!empty($payment->booking->seat_numbers)) {
                $seatNumbers = $payment->booking->seat_numbers;
                $seats = \App\Models\Seat::where('schedule_id', $payment->booking->schedule_id)
                    ->whereIn('seat_number', $seatNumbers)
                    ->get();
                
                foreach ($seats as $seat) {
                    $seat->update(['is_available' => true]);
                }
            }
            $payment->booking->schedule->increment('available_seats', $payment->booking->seat_count ?? 1);
            $payment->booking->update(['status' => 'cancelled']);
        }

        return redirect()->route('admin.payments.pending')
            ->with('success', 'Payment verification updated successfully!');
    }
}
