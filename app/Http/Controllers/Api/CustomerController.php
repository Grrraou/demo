<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Managers\CustomerManager;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerManager $customerManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $customers = $this->customerManager->paginate($perPage);

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ])->validate();

        $customer = $this->customerManager->create($validated);

        return response()->json($customer, 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->customerManager->find($id);
        if (! $customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerManager->find($id);
        if (! $customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ])->validate();

        $this->customerManager->update($customer, $validated);

        return response()->json($customer->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = $this->customerManager->find($id);
        if (! $customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $this->customerManager->delete($customer);

        return response()->json(null, 204);
    }
}
