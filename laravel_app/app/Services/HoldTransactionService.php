<?php

namespace App\Services;

use App\Models\HoldTransaction;
use Illuminate\Support\Facades\DB;

class HoldTransactionService
{
    /**
     * Hold a transaction: Save cart contents and clear active cart.
     */
    public function hold(array $cartLines, ?int $kasirId, ?string $pelanggan, ?string $catatan, float $total): HoldTransaction
    {
        return DB::transaction(function () use ($cartLines, $kasirId, $pelanggan, $catatan, $total) {
            $code = HoldTransaction::generateCode();

            $hold = HoldTransaction::create([
                'code' => $code,
                'kasir_id' => $kasirId,
                'pelanggan' => $pelanggan ?: 'Umum',
                'catatan' => $catatan,
                'cart_data' => $cartLines,
                'total' => $total,
                'status' => 'hold',
            ]);

            // Clear session cart
            session()->forget('pos_cart');
            session()->forget('hold_transaction_id');

            return $hold;
        });
    }

    /**
     * Resume a held transaction: Load saved cart data into session.
     */
    public function resume(int $holdId): HoldTransaction
    {
        $hold = HoldTransaction::findOrFail($holdId);

        // Restore cart data to session
        session(['pos_cart' => $hold->cart_data]);
        session(['hold_transaction_id' => $hold->id]);

        return $hold;
    }

    /**
     * Delete a held transaction.
     */
    public function delete(int $holdId): bool
    {
        $hold = HoldTransaction::find($holdId);
        if ($hold) {
            $hold->delete();
            return true;
        }
        return false;
    }

    /**
     * Auto delete hold transactions older than 24 hours.
     */
    public function autoDeleteOldHolds(): void
    {
        HoldTransaction::where('created_at', '<', now()->subHours(24))->delete();
    }
}
