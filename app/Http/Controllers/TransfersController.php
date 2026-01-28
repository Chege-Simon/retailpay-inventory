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
use Illuminate\Support\Str;

class TransfersController extends Controller
{
    public function index(Request $request)
    {
        $query = Transfer::with(['fromStore.branch', 'toStore.branch', 'product', 
            'requestedByUser',
            'forwardedByUser',
            'approvedByUser',
            'shippedByUser',
            'receivedByUser']);

        if(Auth::user()->role != 'admin') {
            $query->whereHas('fromStore', function ($q) {
                $q->where('branch_id', Auth::user()->branch_id);
                })
                ->orWhereHas('toStore', function ($q) {
                    $q->where('branch_id', Auth::user()->branch_id);
                });
        };
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
        $stores_query = Store::with('branch')->where('is_active', true);
        
        if (Auth::user()->role != 'admin') {
            $stores_query->where('branch_id', Auth::user()->branch_id);
        }
        $stores = $stores_query->get();

        return view('transfers.index', compact('transfers', 'stores'));
    }

    public function create()
    {
        $stores_query = Store::with('branch')->where('is_active', true);
        
        if (Auth::user()->role != 'admin') {
            $stores_query->where('branch_id', Auth::user()->branch_id);
        }
        $stores = $stores_query->get();
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
                'status' => 'approved',
                'initiated_by' => Auth::id(),
                'transfer_date' => now(),
                'notes' => $validated['notes'] ?? null,
                'uuid' => Str::uuid()->toString()
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
    
    public function addrequest()
    {
        $stores_query = Store::with('branch')->where('is_active', true);
        
        if (Auth::user()->role != 'admin') {
            $stores_query->where('branch_id', Auth::user()->branch_id);
        }
        $stores = $stores_query->get();
        $products = Product::where('is_active', true)->get();

        return view('transfers.request', compact('stores', 'products'));
    }

    public function requestRestock(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id', // Store requesting restock
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if(Auth::user()->store_id != $validated['store_id']) {
            return back()->with('error', 'You cannot request stock transfer.');
        }

        DB::beginTransaction();
        try {
            $transfer = Transfer::create([
                'transfer_number' => Transfer::generateTransferNumber(),
                'from_store_id' => null, // Will be set by admin
                'to_store_id' => $validated['store_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'status' => 'requested', // Initial status
                'requested_by' => Auth::id(),
                'requested_date' => now(),
                'notes' => $validated['notes'] ?? null,
                'uuid' => Str::uuid()->toString()
            ]);

            DB::commit();

            return redirect()->route('transfers.show', $transfer)
                ->with('success', 'Restock request submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Request failed: ' . $e->getMessage());
        }
    }

    
    public function forwardToAdmin(Transfer $transfer)
    {
        if ($transfer->status !== 'requested') {
            return back()->with('error', 'Only requested transfers can be forwarded.');
        }
        
        if(Auth::user()->branch_id != $transfer->toStore->branch_id) {
            return back()->with('error', 'You cannot forward stock transfer request.');
        }

        $transfer->update([
            'status' => 'pending_admin_approval',
            'forwarded_by' => Auth::id(),
            'forwarded_date' => now(),
        ]);

        return redirect()->route('transfers.show', $transfer)
            ->with('success', 'Request forwarded to administrator successfully!');
    }

    
    public function approveAndAssignSource(Request $request, Transfer $transfer)
    {
        if ($transfer->status !== 'pending_admin_approval') {
            return back()->with('error', 'Only pending admin approval transfers can be processed.');
        }

        $validated = $request->validate([
            'from_store_id' => 'required|exists:stores,id|different:to_store_id',
        ]);

        if(!Auth::user()->role == 'admin') {
            return back()->with('error', 'You cannot approve and assign source store for stock transfer request.');
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($transfer->product_id);
            $fromStore = Store::findOrFail($validated['from_store_id']);
            
            // Check stock availability
            $inventory = Inventory::where('product_id', $product->id)
                ->where('store_id', $fromStore->id)
                ->first();

            if (!$inventory || $inventory->quantity < $transfer->quantity) {
                throw new \Exception("Insufficient stock in selected source store for product: {$product->name}");
            }

            $transfer->update([
                'from_store_id' => $validated['from_store_id'],
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('transfers.show', $transfer)
                ->with('success', 'Transfer approved and source store assigned successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }
    
    public function acknowledgeShipment(Transfer $transfer)
    {
        if ($transfer->status !== 'approved') {
            return back()->with('error', 'Only approved transfers can be shipped.');
        }

        if(Auth::user()->branch_id != $transfer->fromStore->branch_id) {
            return back()->with('error', 'You cannot acknowledge shipment for stock transfer request.');
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

            $transfer->update([
                'status' => 'in_transit',
                'shipped_by' => Auth::id(),
                'shipped_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('transfers.show', $transfer)
                ->with('success', 'Shipment acknowledged successfully! Items deducted from inventory.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to acknowledge shipment: ' . $e->getMessage());
        }
    }
    
    public function acknowledgeReceipt(Transfer $transfer)
    {
        if ($transfer->status !== 'in_transit') {
            return back()->with('error', 'Only in-transit transfers can be received.');
        }

        // Verify user is from the destination store
        if(Auth::user()->branch_id != $transfer->toStore->branch_id) {
            return back()->with('error', 'You cannot acknowledge receipt for stock transfer request.');
        }


        DB::beginTransaction();
        try {
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
                ->with('success', 'Receipt acknowledged successfully! Items added to inventory.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to acknowledge receipt: ' . $e->getMessage());
        }
    }

    public function show(Transfer $transfer)
    {
        $transfer->load([
            'fromStore.branch', 
            'toStore.branch', 
            'product', 
            'requestedByUser',
            'forwardedByUser',
            'approvedByUser',
            'shippedByUser',
            'receivedByUser'
        ]);
        
        return view('transfers.show', compact('transfer'));
    }

    public function cancel(Transfer $transfer)
    {
        if (in_array($transfer->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This transfer cannot be cancelled.');
        }

        // Add authorization checks based on status
        if ($transfer->status === 'in_transit') {
            $this->authorize('isAdministrator'); // Only admin can cancel in-transit
        }

        $transfer->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_date' => now(),
        ]);

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