<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Visitor;
use App\Models\ResultVisitor;
use App\Models\Seller;
use App\Models\Customer;
use App\Models\AdminSeller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // Import the Validator facade


class VisitorController extends Controller
{
    private Visitor $visitor;
    private Seller $seller;
    private Customer $customer;

    public function __construct(Visitor $visitor, Seller $seller, Customer $customer)
    {
        $this->visitor = $visitor;
        $this->seller = $seller;
        $this->customer = $customer;
    }

    /**
     * Fetch visitors based on seller_id and date filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Get the authenticated user (assuming the user is a seller)
        $user = Auth::user();
        
        // Check if the authenticated user is a seller
        if (!$user || $user->role !== 'seller') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Initialize query for Visitor model
        $query = Visitor::query();

        // Automatically filter by the authenticated seller's ID
        $query->where('seller_id', $user->id);

        // Filter by additional seller_id if provided (optional)
        if ($request->has('seller_id') && $request->seller_id) {
            $query->where('seller_id', $request->seller_id);
        }

        // Filter by date range if both from_date and to_date are provided
        if ($request->has('from_date') && $request->from_date && $request->has('to_date') && $request->to_date) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date') && $request->from_date) {
            // If only from_date is provided, filter visitors after the from_date
            $query->whereDate('date', '>=', $request->from_date);
        } elseif ($request->has('to_date') && $request->to_date) {
            // If only to_date is provided, filter visitors before the to_date
            $query->whereDate('date', '<=', $request->to_date);
        }

        // Fetch the filtered visitors and paginate the result
        $visitors = $query->with('seller', 'customer')->get();

        // Return the visitors as a JSON response
        return response()->json([
            'status' => 'success',
            'visitors' => $visitors
        ], 200);
    }
     public function indexresultvisitor(Request $request): JsonResponse
    {
        // Get the authenticated user (assuming the user is a seller)
        $user = Auth::user();
        
        // Check if the authenticated user is a seller
        if (!$user || $user->role !== 'seller') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Initialize query for Visitor model
        $query = ResultVisitor::query();

        // Automatically filter by the authenticated seller's ID
        $query->where('admin_id', $user->id);

        // Filter by additional seller_id if provided (optional)
        if ($request->has('admin_id') && $request->admin_id) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by date range if both from_date and to_date are provided
        if ($request->has('from_date') && $request->from_date && $request->has('to_date') && $request->to_date) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date') && $request->from_date) {
            // If only from_date is provided, filter visitors after the from_date
            $query->whereDate('date', '>=', $request->from_date);
        } elseif ($request->has('to_date') && $request->to_date) {
            // If only to_date is provided, filter visitors before the to_date
            $query->whereDate('date', '<=', $request->to_date);
        }

        // Fetch the filtered visitors and paginate the result
        $visitors = $query->with('seller', 'customer')->get();

        // Return the visitors as a JSON response
        return response()->json([
            'status' => 'success',
            'visitors' => $visitors
        ], 200);
    }
public function store(Request $request): JsonResponse
{
    // Get the authenticated seller
    $user = Auth::user();
    
    // Ensure the user is authorized to perform this action
    if (!$user || $user->role !== 'seller') {
        return response()->json(['error' => 'Unauthorized access'], 403);
    }

    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'customer_id' => 'required|exists:customers,id',
        'note' => 'required|string',
        'lang' => 'nullable', // Optional, ensure it's a valid number
        'lat' => 'nullable',  // Optional, ensure it's a valid number
    ]);

    // If validation fails, return a 422 response with errors
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Get the current date and time
    $currentDate = now()->toDateString();
    $currentTime = now()->format('H:i'); // Gets current time in HH:mm format

    // Check if a visit has already been made today before 11 AM
    $lastVisit = ResultVisitor::where('admin_id', $user->id)
        ->whereDate('created_at', $currentDate)
        ->first();

    // Check if the visit is before 11 AM
    if ($currentTime < '11:00' && !$lastVisit) {
        // Increment the `number_of_days` if it's the first visit of the day before 11 AM
        $user->increment('number_of_days');
    }

    // Store the new visitor result
    $resultVisitor = ResultVisitor::create([
        'customer_id' => $request->customer_id,
        'admin_id' => $user->id, // Admin ID is the authenticated seller's ID
        'note' => $request->note,
        'lang' => $request->lang ?? 0,
        'lat' => $request->lat ?? 0,
    ]);

    // Increment the `result_visitors` column in the `admins` table
    $user->increment('result_visitors'); // This will increment the column by 1

    // Return a success response with the created visitor data
    return response()->json([
        'status' => 'success',
        'visitor' => $resultVisitor
    ], 200);
}

public function storeseller(Request $request)
{
    try {
        // Fetch the authenticated seller
        $sellerId = auth()->id();

        // Fetch the associated admin_id
        $adminId = AdminSeller::where('seller_id', $sellerId)->first();
        if (!$adminId) {
            return response()->json(['error' => 'Admin not found for the seller'], 404);
        }

        // Use Validator to validate the input fields
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id', // Ensure customer_id exists in customers table
            'date' => 'required|date',                      // Validate as a valid date
            'note' => 'required|string',                    // Validate note as a string
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new visitor record
        $visitor = new Visitor;
        $visitor->seller_id = $sellerId;
        $visitor->customer_id = $request->customer_id; // Single value, not an array
        $visitor->date = $request->date;              // Single date value
        $visitor->note = $request->note;              // Single note value
        $visitor->save();

        // Increment the seller's visitors count by 1
        Seller::find($sellerId)->increment('visitors');

        // Return a JSON response with the results
        return response()->json([
            'status' => 'success',
            'visitor' => $visitor,
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging purposes

        // Return an error response
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while processing the request. Please try again later.'.$e,
        ], 500);
    }
}



}
