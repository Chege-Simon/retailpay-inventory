<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransfersController extends Controller
{
    public function index(Request $request)
    {
        $query = Transfer::with(['fromStore.branch', 'toStore.branch', 'product', 'initiatedByUser']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('store_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_store_id', $request->store_id)
                  ->orWhere('to_store_id', $request->store_id);
            });
        }

        $transfers = $query->latest()->paginate(20);
        $stores = Store::with('branch')->get();

        return view('transfers.index', compact('transfers', 'stores'));
    }

    public function create()
    {
        $stores = Store::with('branch')->where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('transfers.create', compact('stores', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id' => 'required|exists:stores,id|different:from_store_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($validated['product_id']);
            $fromStore = Store::findOrFail($validated['from_store_id']);
            
            $inventory = Inventory::where('product_id', $product->id)
                ->where('store_id', $fromStore->id)
                ->first();

            if (!$inventory || $inventory->quantity < $validated['quantity']) {
                throw new \Exception("Insufficient stock in source store for product: {$product->name}");
            }

            $transfer = Transfer::create([
                'transfer_number' => Transfer::generateTransferNumber(),
                'from_store_id' => $validated['from_store_id'],
                'to_store_id' => $validated['to_store_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'status' => 'pending',
                'initiated_by' => Auth::id(),
                'transfer_date' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('transfers.show', $transfer)
                ->with('success', 'Transfer initiated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function show(Transfer $transfer)
    {
        $transfer->load(['fromStore.branch', 'toStore.branch', 'product', 'initiatedByUser', 'receivedByUser']);
        return view('transfers.show', compact('transfer'));
    }

    public function complete(Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be completed.');
        }

        DB::beginTransaction();

        try {
            // Deduct from source store
            StockMovement::record(
                $transfer->product,
                $transfer->fromStore,
                'transfer_out',
                -$transfer->quantity,
                $transfer,
                Auth::user(),
                "Transfer Out: {$transfer->transfer_number}"
            );

            // Add to destination store
            StockMovement::record(
                $transfer->product,
                $transfer->toStore,
                'transfer_in',
                $transfer->quantity,
                $transfer,
                Auth::user(),
                "Transfer In: {$transfer->transfer_number}"
            );

            $transfer->update([
                'status' => 'completed',
                'received_by' => Auth::id(),
                'received_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('transfers.show', $transfer)
                ->with('success', 'Transfer completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete transfer: ' . $e->getMessage());
        }
    }

    public function cancel(Transfer $transfer)
    {
        if (!in_array($transfer->status, ['pending', 'in_transit'])) {
            return back()->with('error', 'Only pending or in-transit transfers can be cancelled.');
        }

        $transfer->update(['status' => 'cancelled']);

        return redirect()->route('transfers.show', $transfer)
            ->with('success', 'Transfer cancelled successfully!');
    }

    public function getStoreInventory($storeId)
    {
        $inventory = Inventory::with('product')
            ->where('store_id', $storeId)
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'sku' => $item->product->sku,
                    'name' => $item->product->name,
                    'available_quantity' => $item->quantity,
                ];
            });

        return response()->json($inventory);
    }
}