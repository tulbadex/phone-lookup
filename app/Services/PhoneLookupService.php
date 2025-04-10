<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\PhoneLookup;
use Illuminate\Support\Facades\Log;

class PhoneLookupService
{
    protected $client;
    protected $apiKey;
    protected $countryCallingCodes = [
        'US' => '1',
        'GB' => '44',
        'CA' => '1',
        'AU' => '61',
        'DE' => '49',
        'FR' => '33',
        'JP' => '81',
        'CN' => '86',
        'BR' => '55',
        'RU' => '7',
        'IN' => '91',
        'IT' => '39',
        'ES' => '34',
        'MX' => '52',
        // Add more country codes as needed
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false
        ]);
        $this->apiKey = env('NUMVERIFY_API_KEY');
    }

    public function lookup($phoneNumber, $countryCode = null)
    {
        try {
            $phoneNumber = $this->normalizePhoneNumber($phoneNumber, $countryCode);

            // Check cache first
            $cached = PhoneLookup::where('phone_number', $phoneNumber)->first();
            if ($cached) {
                return $cached;
            }

            // Extract country code from normalized phone number for API call
            $apiCountryCode = $this->extractCountryCodeFromNumber($phoneNumber);

            // Call API
            $response = $this->client->get("http://apilayer.net/api/validate", [
                'query' => [
                    'access_key' => $this->apiKey,
                    'number' => $phoneNumber,
                    'country_code' => $apiCountryCode ?? '',
                    'format' => 1
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("API request failed with status: ".$response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['valid'])) {
                throw new \Exception("Invalid API response format");
            }
            
            if (!$data['valid']) {
                $errorMsg = $data['error']['info'] ?? 'Invalid phone number';
                throw new \Exception($errorMsg);
            }

            // Store in database
            return PhoneLookup::create([
                'phone_number' => $phoneNumber,
                'type' => $data['line_type'] ?? null,
                'carrier' => $data['carrier'] ?? null,
                'location' => $data['location'] ?? null,
                'country_name' => $data['country_name'] ?? null,
                'country_code' => $data['country_code'] ?? null,
                'raw_data' => json_encode($data)
            ]);

        } catch (\Exception $e) {
            Log::error("Phone lookup failed for {$phoneNumber}: " . $e->getMessage());
            throw new \Exception("Could not validate phone number: " . $e->getMessage());
        }
    }

    protected function normalizePhoneNumber($phoneNumber, $countryCode = null)
    {
        // Remove all non-digit characters except + and comma (for CSV handling)
        $cleaned = preg_replace('/[^0-9+,]/', '', $phoneNumber);
        
        if (empty($cleaned)) {
            throw new \Exception("Empty phone number after normalization");
        }

        // If it already starts with +, return as is (international format)
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // Handle CSV format (number,country)
        if (strpos($cleaned, ',') !== false) {
            $parts = explode(',', $cleaned, 2);
            $phonePart = trim($parts[0]);
            $countryPart = trim($parts[1] ?? '');
            
            if (!empty($countryPart)) {
                $countryCode = $countryPart;
            }
        } else {
            $phonePart = $cleaned;
        }

        // Remove any remaining non-digit characters
        $phoneDigits = preg_replace('/[^0-9]/', '', $phonePart);

        // Handle cases where country code is provided
        if ($countryCode) {
            $countryCode = strtoupper($countryCode);
            if (isset($this->countryCallingCodes[$countryCode])) {
                $countryPrefix = $this->countryCallingCodes[$countryCode];
                
                // Remove leading zeros if present
                $phoneDigits = ltrim($phoneDigits, '0');
                
                return '+' . $countryPrefix . $phoneDigits;
            }
        }

        // Default to US if no country code specified
        // If it's 10 digits, assume US number and add +1
        if (strlen($phoneDigits) === 10) {
            return '+1' . $phoneDigits;
        }
        
        // If it's 11 digits starting with 1 (US), add +
        if (strlen($phoneDigits) === 11 && str_starts_with($phoneDigits, '1')) {
            return '+' . $phoneDigits;
        }
        
        throw new \Exception("Phone number must be in international format (start with +) or include valid country code");
    }

    protected function extractCountryCodeFromNumber($phoneNumber)
    {
        if (preg_match('/^\+\d{1,3}/', $phoneNumber, $matches)) {
            $countryPrefix = substr($matches[0], 1); // Remove the +
            foreach ($this->countryCallingCodes as $code => $prefix) {
                if ($prefix === $countryPrefix) {
                    return $code;
                }
            }
        }
        return null;
    }
}