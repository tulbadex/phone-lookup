<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PhoneLookupService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class PhoneLookupController extends Controller
{
    public function __construct(protected PhoneLookupService $lookupService)
    {
    }

    public function index()
    {
        return view('phone-lookup');
    }

    public function lookup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => [
            'required_without:file',
            'string',
            function ($attribute, $value, $fail) {
                // Allow formats like:
                // +1234567890
                // 1234567890
                // 1234567890,US
                if (!preg_match('/^(\+?[0-9]{10,15}|[0-9]{10,15},[A-Z]{2})$/', $value)) {
                    $fail('Please enter a valid phone number (10-15 digits, optionally starting with +) or in format "number,CC"');
                }
            },
        ],
            'file' => 'required_without:phone_number|file|mimes:csv,txt|max:1024',
            'country_code' => 'sometimes|string|size:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('file')) {
                return $this->processBulkLookup($request);
            }

            $result = $this->lookupService->lookup(
                $request->phone_number,
                $request->country_code
            );

            return response()->json([
                'success' => true,
                'data' => $this->formatResult($result)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    protected function processBulkLookup(Request $request)
    {
        $file = $request->file('file');
        $content = file_get_contents($file->path());
        $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $content)));
        
        $results = [];
        $errors = [];
        
        foreach ($lines as $index => $line) {
            if (empty($line)) continue;
            
            try {
                // Remove any whitespace and parse the line
                $line = trim($line);
                
                // Initialize variables
                $phoneNumber = $line;
                $countryCode = null;

                // Check if line contains a comma (CSV format)
                if (strpos($line, ',') !== false) {
                    $parts = explode(',', $line, 2);
                    $phoneNumber = trim($parts[0]);
                    $countryCode = trim($parts[1] ?? '');
                    
                    // If country code is empty, set to null (will default to US)
                    if (empty($countryCode)) {
                        $countryCode = null;
                    }
                }
                
                if (empty($phoneNumber)) {
                    throw new \Exception("Empty phone number");
                }
                
                $result = $this->lookupService->lookup($phoneNumber, $countryCode);
                $results[] = $this->formatResult($result);
            } catch (\Exception $e) {
                $errors[] = [
                    'phone' => $line,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Rest of the method remains the same...
        // Paginate results
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 5);
        $paginated = new LengthAwarePaginator(
            array_slice($results, ($page - 1) * $perPage, $perPage),
            count($results),
            $perPage,
            $page,
            ['path' => $request->url()]
        );
        
        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'processed_count' => count($results),
                'error_count' => count($errors)
            ],
            'errors' => $errors
        ]);
    }

    protected function formatResult($result)
    {
        return [
            'phone_number' => $result->phone_number,
            'type' => $result->type ?? 'Unknown',
            'carrier' => $result->carrier ?? 'Unknown',
            'location' => $result->location ?? 'Unknown',
            'country_name' => $result->country_name ?? 'Unknown',
            'country_code' => $result->country_code ?? null
        ];
    }
}