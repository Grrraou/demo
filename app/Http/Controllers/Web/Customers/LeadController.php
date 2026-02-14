<?php

namespace App\Http\Controllers\Web\Customers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        return view('customers.leads.index');
    }
}
