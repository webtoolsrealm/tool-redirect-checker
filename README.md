Redirect Checker

Trace the full redirect chain of any URL and see every hop, status code, and final destination.

Given a URL, this library follows each `3xx` response and records what happened at every
step — the status code, the `Location` header, and the time taken. It stops when it reaches
a non-redirecting response, hits the hop limit, or detects a loop.

Useful for debugging migrations, verifying `301` vs `302` behaviour, checking HTTPS
enforcement, and expanding shortened links before you visit them.

A hosted version runs at [webtoolsrealm.com/tool/redirect-checker](https://webtoolsrealm.com/tool/redirect-checker).

## Features

- Follows the complete redirect chain, not just the final URL
- Records status code, `Location` header, and response time for every hop
- Detects redirect loops and stops instead of hanging
- Configurable hop limit
- Configurable timeout and user agent
- No external API — makes the requests itself

## Requirements

- PHP 8.0 or higher
- ext-curl
- Guzzle 7.x

## Installation

```bash
git clone https://github.com/baleeghuddin/tool-redirect-checker.git
cd tool-redirect-checker
composer install
```

## Usage

```php
use Baleeghuddin\RedirectChecker\RedirectChecker;

$checker = new RedirectChecker();
$result  = $checker->check('http://example.com');

echo $result->finalUrl();   // https://www.example.com/
echo $result->hopCount();   // 2
echo $result->isLoop();     // false
```

### Inspecting each hop

```php
foreach ($result->hops() as $hop) {
    printf(
        "%d  %s  ->  %s  (%dms)\n",
        $hop->statusCode(),
        $hop->url(),
        $hop->location(),
        $hop->durationMs()
    );
}
```

Output:

301 http://example.com -> https://example.com (142ms)
301 https://example.com -> https://www.example.com/ (98ms)
200 https://www.example.com/ -> - (110ms)


### Options

```php
$checker = new RedirectChecker([
    'max_hops'   => 20,
    'timeout'    => 10,
    'user_agent' => 'MyApp/1.0',
    'verify_ssl' => true,
]);
```

| Option | Default | Description |
|---|---|---|
| `max_hops` | `10` | Stop after this many redirects |
| `timeout` | `10` | Per-request timeout in seconds |
| `user_agent` | Library default | Sent with every request |
| `verify_ssl` | `true` | Verify TLS certificates |

## Handling loops

When a URL appears twice in the chain, the checker stops and marks the result:

```php
$result = $checker->check('https://example.com/loop');

if ($result->isLoop()) {
    echo "Loop detected at: " . $result->loopUrl();
}
```

## Notes and limitations

- Redirects are followed from a single network location. Geo-based or device-based
  redirects may resolve differently elsewhere.
- JavaScript and meta refresh redirects are not followed — only HTTP `3xx` responses.
- Servers that block non-browser user agents may return `403` regardless of their real
  redirect behaviour. Set a browser `user_agent` if you hit this.
- Sending requests to hosts you do not control may be restricted in some jurisdictions.
  Check before scanning at scale.

## License

MIT — see [LICENSE](LICENSE).

## Author

Built by [Baleeghuddin](https://github.com/baleeghuddin) while working on
[Web Tools Realm](https://webtoolsrealm.com), a set of free browser-based network
diagnostic tools.
