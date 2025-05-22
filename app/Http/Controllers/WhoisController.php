<?php

namespace App\Http\Controllers;

use App\Services\WhoisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhoisController extends Controller
{
    /**
     * Get WHOIS information for a domain.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookup(Request $request,
                           WhoisService $whoisService): JsonResponse
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $domain = trim($request->input('domain'));

        try {
            $rootDomain = $whoisService->getRootDomain($domain);
            $raw = $whoisService->loadWhois($rootDomain);

            if (!$raw || stripos($raw, 'No entries found') !== false || stripos($raw, 'not found') !== false) {
                return response()->json(['error' => 'WHOIS lookup failed'], 404);
            }

            return response()->json([
                'domain'        => $whoisService->extractField($raw, 'Domain Name') ?? $rootDomain,
                'created_at'    => $whoisService->extractField($raw, 'Creation Date'),
                'updated_at'    => $whoisService->extractField($raw, 'Updated Date'),
                'expires_at'    => $whoisService->extractField($raw, 'Registry Expiry Date'),
                'registrar'     => $whoisService->extractField($raw, 'Registrar'),
                'status'        => $whoisService->extractFields($raw, 'Domain Status'),
                'name_servers'  => $whoisService->extractFields($raw, 'Name Server'),
                'dnssec'        => $whoisService->extractField($raw, 'DNSSEC'),
                'whois_raw'     => $raw,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error'   => 'WHOIS_LOOKUP_FAILED'
            ], 500);
        }
    }
}
