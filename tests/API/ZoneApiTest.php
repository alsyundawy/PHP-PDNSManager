<?php
declare(strict_types=1);
namespace Tests\API;
use PHPUnit\Framework\TestCase;
use App\Core\Application;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class ZoneApiTest extends TestCase
{
    public function testGetZonesWithoutAuth(): void {
        $app = new Application(__DIR__ . '/../../');
        $request = new ServerRequest('GET', new Uri('/api/v1/zones'));
        $response = $app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());
    }
}
