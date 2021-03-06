<?php
namespace RKA\Middleware\Test;

use RKA\Middleware\IpAddress;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use RuntimeException;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    public function testIpSetByRemoteAddr()
    {
        $middleware = new IPAddress(false, [], 'IP');

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
        ]);
        $response = new Response();

        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('IP');
            return $response;
        });

        $this->assertSame('192.168.1.1', $ipAddress);
    }

    public function testIpIsNullIfMissing()
    {
        $middleware = new IPAddress();

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertNull($ipAddress);
    }

    public function testXForwardedForIp()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.3, 192.168.1.2, 192.168.1.1'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.1.3', $ipAddress);
    }

    public function testProxyIpIsIgnored()
    {
        $middleware = new IPAddress();

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.0.1',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.3, 192.168.1.2, 192.168.1.1'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.0.1', $ipAddress);
    }

    public function testHttpClientIp()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_CLIENT_IP' => '192.168.1.3'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.1.3', $ipAddress);
    }

    public function testXForwardedForIpV6()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_FOR' => '001:DB8::21f:5bff:febf:ce22:8a2e'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('001:DB8::21f:5bff:febf:ce22:8a2e', $ipAddress);
    }

    public function testXForwardedForWithInvalidIp()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_FOR' => 'foo-bar'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.1.1', $ipAddress);
    }

    public function testXForwardedForIpWithTrustedProxy()
    {
        $middleware = new IPAddress(true, ['192.168.0.1', '192.168.0.2']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.0.2',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.3, 192.168.1.2, 192.168.1.1'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.1.3', $ipAddress);
    }

    public function testXForwardedForIpWithUntrustedProxy()
    {
        $middleware = new IPAddress(true, ['192.168.0.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.0.2',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.3, 192.168.1.2, 192.168.1.1'
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.0.2', $ipAddress);
    }

    public function testForwardedWithMultipleFor()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_FORWARDED' => 'for=192.0.2.43, for=198.51.100.17;by=203.0.113.60;proto=http;host=example.com',
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.0.2.43', $ipAddress);
    }

    public function testForwardedWithAllOptions()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_FORWARDED' => 'for=192.0.2.60; proto=http;by=203.0.113.43; host=_hiddenProxy, for=192.0.2.61',
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.0.2.60', $ipAddress);
    }

    public function testForwardedWithWithIpV6()
    {
        $middleware = new IPAddress(true, ['192.168.1.1']);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_FORWARDED' => 'For="[2001:db8:cafe::17]:4711", for=_internalProxy',
        ]);
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('2001:db8:cafe::17', $ipAddress);
    }

    public function testCustomHeader()
    {
        $headersToInspect = [
            'Foo-Bar'
        ];
        $middleware = new IPAddress(true, ['192.168.0.1'], null, $headersToInspect);

        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.0.1',
        ]);
        $request = $request->withAddedHeader('Foo-Bar', '192.168.1.3');
        $response = new Response();

        $ipAddress = '123';
        $response  = $middleware($request, $response, function ($request, $response) use (&$ipAddress) {
            // simply store the "ip_address" attribute in to the referenced $ipAddress
            $ipAddress = $request->getAttribute('ip_address');
            return $response;
        });

        $this->assertSame('192.168.1.3', $ipAddress);
    }
}
