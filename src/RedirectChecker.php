<?php

namespace Baleeghuddin\RedirectChecker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RedirectChecker
{
    private Client $client;
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'max_hops'   => 10,
            'timeout'    => 10,
            'user_agent' => 'RedirectChecker/1.0 (+https://webtoolsrealm.com)',
            'verify_ssl' => true,
        ], $options);

        $this->client = new Client([
            'allow_redirects' => false,
            'http_errors'     => false,
            'timeout'         => $this->options['timeout'],
            'verify'          => $this->options['verify_ssl'],
            'headers'         => [
                'User-Agent' => $this->options['user_agent'],
            ],
        ]);
    }

    public function check(string $url): Result
    {
        $hops    = [];
        $seen    = [];
        $current = $url;
        $loopUrl = null;

        for ($i = 0; $i <= $this->options['max_hops']; $i++) {
            if (isset($seen[$current])) {
                $loopUrl = $current;
                break;
            }

            $seen[$current] = true;
            $start = microtime(true);

            try {
                $response = $this->client->request('GET', $current);
            } catch (GuzzleException $e) {
                $hops[] = new Hop($current, 0, null, 0, $e->getMessage());
                break;
            }

            $duration = (int) round((microtime(true) - $start) * 1000);
            $status   = $response->getStatusCode();
            $location = $response->getHeaderLine('Location') ?: null;

            if ($location !== null) {
                $location = $this->resolveUrl($current, $location);
            }

            $hops[] = new Hop($current, $status, $location, $duration);

            if ($status < 300 || $status >= 400 || $location === null) {
                break;
            }

            $current = $location;
        }

        return new Result($url, $hops, $loopUrl);
    }

    private function resolveUrl(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $parts  = parse_url($base);
        $scheme = $parts['scheme'] ?? 'http';
        $host   = $parts['host'] ?? '';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $origin = $scheme . '://' . $host . $port;

        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }

        $path = $parts['path'] ?? '/';
        $dir  = rtrim(dirname($path), '/');

        return $origin . $dir . '/' . $location;
    }
}
