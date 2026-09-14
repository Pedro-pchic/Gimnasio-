<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        return view('invoices.index', [
            'sales' => Sale::query()
                ->with(['client', 'details', 'payments', 'receipt'])
                ->orderByDesc('sale_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function show(Sale $sale): View
    {
        return view('invoices.show', [
            'sale' => $sale->load(['client', 'branch', 'details', 'payments', 'receipt']),
        ]);
    }
}
