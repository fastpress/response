<?php declare(strict_types=1);

/**
 * HTTP response object
 *
 * PHP version 7.0
 *
 * @category   fastpress
 *
 * @author     https://github.com/samayo
 * @copyright  Copyright (c) samayo
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @version    0.1.0
 */
namespace Fastpress\Http;

/**
 * HTTP response object
 *
 * @category   fastpress
 *
 * @author     https://github.com/samayo
 */
class Response
{
    private int $code = 200;
    private string $text = "OK";
    private array $headers = [];
    private string $protocol = "HTTP/1.1";
    private ?string $body = null;

    /**
     * Set the response code and text.
     *
     * @param int    $code
     * @param string $text
     */
    public function setResponse(int $code = 200, string $text = "OK"): void
    {
        $this->code = $code;
        $this->text = $text;
    }

    /**
     * Set the response body.
     *
     * @param string|null $body
     *
     * @return Response
     */
    public function setBody(?string $body): Response
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Add a header to the response.
     *
     * @param string $name
     * @param string $value
     *
     * @return Response
     */
    public function addHeader(string $name, string $value): Response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set the response code.
     *
     * @param int $code
     *
     * @return Response
     */
    public function setCode(int $code): Response
    {
        if ($code < 100 || $code > 599) {
            throw new \LogicException(sprintf(
                "%s is an unsupported HTTP status code",
                $code
            ));
        }

        $this->code = $code;
        return $this;
    }

    /**
     * Get the response code.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Get the response text.
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Refresh the page.
     */
    public function refresh(): void
    {
        header("Refresh:0");
    }

    /**
     * Get the response headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Render the response.
     *
     * @return string|null
     */
    public function render(): ?string
    {
        if ($this->canSendHeaders()) {
            header($this->fullHeaderStatus());
            $this->renderHeaders();
        }

        return $this->body;
    }

    /**
     * Render the response headers.
     */
    protected function renderHeaders(): void
    {
        foreach ($this->headers as $key => $headerValue) {
            header($key . ": " . $headerValue);
        }
    }

    /**
     * Redirect to a URL with an optional HTTP code.
     *
     * @param string $url
     * @param int    $code
     */
    public function redirect(string $url, int $code = 301): void
    {
        header("Location: " . $url, true, $code);
    }

    /**
     * Disable browser caching for the response.
     *
     * @return Response
     */
    public function disableBrowserCache(): Response
    {
        $this->headers[] = "Cache-Control: no-cache, no-store, must-revalidate";
        $this->headers[] = "Pragma: no-cache";
        $this->headers[] = "Expires: Thu, 26 Feb 1970 20:00:00 GMT";
        return $this;
    }

    /**
     * Get the full header status.
     *
     * @return string
     */
    private function fullHeaderStatus(): string
    {
        return $this->protocol ." ". $this->code ." ". $this->text;
    }

    /**
     * Check if headers can be sent.
     *
     * @return bool
     */
    public function canSendHeaders(): bool
    {
        return !headers_sent();
    }
}
