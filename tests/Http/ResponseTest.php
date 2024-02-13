<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Fastpress\Http\Response;

final class ResponseTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            Response::class,
            new Response()
        );
    }

    public function testDefaultResponseCodeAndText(): void
    {
        $response = new Response();
        $this->assertSame(200, $response->getCode());
        $this->assertSame("OK", $response->getText());
    }

    public function testSetResponse(): void
    {
        $response = new Response();
        $response->setResponse(404, "Not Found");

        $this->assertSame(404, $response->getCode());
        $this->assertSame("Not Found", $response->getText());
    }

    public function testSetAndGetBody(): void
    {
        $response = new Response();
        $response->setBody("Test Body");

        $this->assertSame("Test Body", $response->render());
    }

    public function testAddAndRetrieveHeaders(): void
    {
        $response = new Response();
        $response->addHeader("Content-Type", "application/json");

        $this->assertArrayHasKey("Content-Type", $response->getHeaders());
        $this->assertSame("application/json", $response->getHeaders()["Content-Type"]);
    }

    public function testSetCodeWithInvalidValue(): void
    {
        $this->expectException(\LogicException::class);

        $response = new Response();
        $response->setCode(99);
    }

    public function testDisableBrowserCache(): void
    {
        $response = new Response();
        $response->disableBrowserCache();

        $headers = $response->getHeaders();
        $this->assertContains("Cache-Control: no-cache, no-store, must-revalidate", $headers);
        $this->assertContains("Pragma: no-cache", $headers);
        $this->assertContains("Expires: Thu, 26 Feb 1970 20:00:00 GMT", $headers);
    }

    // Add more tests as needed
}
