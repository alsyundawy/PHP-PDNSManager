<?php

declare(strict_types=1);

namespace App\Services\PowerDNS\Resource;

use App\Services\PowerDNS\PowerDNSClientInterface;

class SearchResource
{
    private const SEARCH_ENDPOINT = 'search-data';

    private PowerDNSClientInterface $client;

    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }

    public function search(string $query, int $max = 20, string $objectType = ''): array
    {
        $params = [
            'q' => $query,
            'max' => $max,
        ];
        if ($objectType !== '') {
            $params['object_type'] = $objectType;
        }

        return $this->client->get(self::SEARCH_ENDPOINT, $params);
    }
}
