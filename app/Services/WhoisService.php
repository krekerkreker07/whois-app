<?php

namespace App\Services;

class WhoisService
{

    private $labelsMap = [
        'Creation Date' => ['Creation Date', 'created'],
        'Updated Date' => ['Updated Date', 'modified'],
        'Registry Expiry Date' => ['Registry Expiry Date', 'expires'],
        'Registrar' => ['Registrar', 'registrar'],
        'Name Server' => ['nserver', 'Name Server'],
        'Domain Status' => ['status', 'Domain Status'],
    ];

    /**
     * Executes a WHOIS query for the given domain and returns the response.
     *
     * @param string $domain The domain name to query.
     * @return string|null The WHOIS query result, or null if the command fails.
     */
    function loadWhois(string $domain): ?string
    {
        $domain = escapeshellarg($domain);
        $tld = $this->getTLD($domain);

        $whoisServers = [
            'com.ua' => 'whois.com.ua',
            'ua'     => 'whois.ua',
        ];

        $server = $whoisServers[$tld] ?? null;

        $command = $server
            ? "whois -h $server $domain"
            : "whois $domain";

        return shell_exec($command);
    }

    function extractField($text, $label): ?string {

        $labelsToTry = $this->labelsMap[$label] ?? [$label];

        foreach ($labelsToTry as $tryLabel) {
            if (preg_match("/^$tryLabel:\s*(.+)$/mi", $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    function extractFields($text, $label): array {
        $labelsToTry = $this->labelsMap[$label] ?? [$label];
        foreach ($labelsToTry as $tryLabel) {
            if (preg_match_all("/^$tryLabel:\s*(.+)$/mi", $text, $matches)){
                return array_map('trim', $matches[1] ?? []);
            }
        }

        return array_map('trim', $matches[1] ?? []);
    }

    function getRootDomain($domain): string {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain);

        $knownSuffixes = ['com.ua', 'net.ua', 'org.ua', 'gov.ua'];

        $suffix = implode('.', array_slice($parts, -2));
        if (in_array($suffix, $knownSuffixes) && count($parts) >= 3) {
            return implode('.', array_slice($parts, -3));
        }

        return implode('.', array_slice($parts, -2));
    }

    function getTLD($domain): string {
        $parts = explode('.', strtolower($domain));
        $count = count($parts);
        return $count >= 2
            ? $parts[$count - 2] . '.' . $parts[$count - 1]
            : end($parts);
    }
}
