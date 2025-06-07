<?php

namespace App\GraphQL\Mutations;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class PaymentMutation
{
    /**
     * Create a new payment.
     */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Payment
    {
        $user = Auth::user();
        $booking = Booking::findOrFail($args['booking_id']);
        $paymentMethod = PaymentMethod::findOrFail($args['payment_method_id']);

        // Check if user owns this booking
        if ($booking->user_id !== $user->id) {
            throw new \Exception('Unauthorized to create payment for this booking.');
        }

        // Check if booking already has a payment
        if ($booking->payment) {
            throw ValidationException::withMessages([
                'booking_id' => ['Payment already exists for this booking.'],
            ]);
        }

        // Check if booking status allows payment
        if ($booking->status !== 'pending') {
            throw ValidationException::withMessages([
                'booking_id' => ['Cannot create payment for this booking status.'],
            ]);
        }

        $paymentProofPath = null;

        // Handle payment proof upload
        if (isset($args['payment_proof'])) {
            $file = $args['payment_proof'];
            $fileName = 'payment_proof_' . $booking->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $paymentProofPath = $file->storeAs('payment_proofs', $fileName, 'public');
        }        // Create payment
        $payment = Payment::create([
            'payment_code' => 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'payment_method' => $paymentMethod->code,
            'amount' => $booking->total_amount,
            'status' => 'pending',
            'proof_image' => $paymentProofPath,
        ]);

        return $payment->load(['user', 'booking.schedule.route', 'paymentMethod']);
    }

    /**
     * Verify payment (admin only).
     */
    public function verify($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Payment
    {
        $admin = Auth::user();
        $payment = Payment::findOrFail($args['id']);

        // Check if user is admin
        if (!$admin->isAdmin()) {
            throw new \Exception('Unauthorized. Admin access required.');
        }        // Update payment status
        $payment->update([
            'status' => $args['status'],
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $args['notes'] ?? null,
        ]);// Update booking status based on payment verification
        if ($args['status'] === 'verified') {
            $payment->booking->update(['status' => 'confirmed']);
        } elseif ($args['status'] === 'rejected') {
            // Make seats available again and increment schedule seats
            if (!empty($payment->booking->seat_numbers)) {
                $seatNumbers = $payment->booking->seat_numbers;
                $seats = Seat::where('schedule_id', $payment->booking->schedule_id)
                    ->whereIn('seat_number', $seatNumbers)
                    ->get();
                
                foreach ($seats as $seat) {
                    $seat->update(['is_available' => true]);
                }
            }
            $payment->booking->schedule->increment('available_seats', $payment->booking->seat_count ?? 1);
            $payment->booking->update(['status' => 'cancelled']);
        }

        return $payment->load(['user', 'booking.schedule.route', 'paymentMethod', 'verifiedBy']);
    }
}
