<?php

declare(strict_types=1);

namespace Fastpress\Http;

/**
 * Represents an HTTP response.
 *
 * This class provides methods for setting the status code, headers, and content of an HTTP response,
 * as well as convenient methods for sending JSON responses, file downloads, and redirects.
 */
class Response
{
    /**
     * @var array<int, string> HTTP status codes and their corresponding messages.
     */
    private const HTTP_MESSAGES = [
        // 1xx Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4xx Client Errors
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        409 => 'Conflict',
        410 => 'Gone',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        // 5xx Server Errors
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];

    /**
     * @var int The HTTP status code of the response.
     */
    private int $statusCode = 200;

    /**
     * @var array<string, string> The headers to be sent with the response.
     */
    private array $headers = [];

    /**
     * @var mixed The content of the response.
     */
    private mixed $content = null;

    /**
     * @var string The content type of the response.
     */
    private string $contentType = 'text/html';

    /**
     * @var string|null The charset of the response.
     */
    private ?string $charset = 'UTF-8';

    /**
     * @var bool Whether the connection is secure (HTTPS).
     */
    private bool $secure = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    /**
     * Sets the content of the response.
     *
     * @param mixed $content The content of the response.
     * @return $this
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;

        // Set Content-Length for string content
        if (is_string($content)) {
            $this->header('Content-Length', (string) mb_strlen($content, '8bit'));
        }

        return $this;
    }

    /**
     * Sets the content type and charset of the response.
     *
     * @param string $contentType The content type of the response.
     * @param string|null $charset The charset of the response.
     * @return $this
     */
    public function setContentType(string $contentType, ?string $charset = null): self
    {
        $this->contentType = $contentType;
        $this->charset = $charset;
        return $this;
    }

    /**
     * Sets the HTTP status code of the response.
     *
     * @param int $code The HTTP status code.
     * @return $this
     * @throws \InvalidArgumentException If the status code is invalid.
     */
    public function setStatusCode(int $code): self
    {
        if (!isset(self::HTTP_MESSAGES[$code])) {
            throw new \InvalidArgumentException("Invalid HTTP status code: {$code}");
        }

        $this->statusCode = $code;
        return $this;
    }

    /**
     * Adds a header to the response.
     *
     * @param string $name The name of the header.
     * @param string $value The value of the header.
     * @return $this
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Sends a JSON response.
     *
     * @param mixed $data The data to be encoded as JSON.
     * @param int $status The HTTP status code.
     * @return $this
     */
    public function json(mixed $data, int $status = 200): self
    {
        try {
            $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return $this->setStatusCode($status)
                ->setContentType('application/json')
                ->setContent($content);
        } catch (\JsonException $e) {
            return $this->withError('Failed to encode JSON response', 500);
        }
    }

    /**
     * Sends a file download response.
     *
     * @param string $filepath The path to the file to be downloaded.
     * @param string|null $filename The filename to be used for the download.
     * @throws \RuntimeException If the file is not found or not readable.
     */
    public function download(string $filepath, ?string $filename = null): void
    {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            throw new \RuntimeException("File not found or not readable: {$filepath}");
        }

        $filename = $filename ?? basename($filepath);
        $encodedFilename = rawurlencode($filename);
        $mimeType = mime_content_type($filepath) ?: 'application/octet-stream';
        $filesize = filesize($filepath);

        if ($filesize === false) {
            throw new \RuntimeException("Unable to determine file size: {$filepath}");
        }

        $this->setContentType($mimeType, null)
            ->header('Content-Disposition', "attachment; filename*=UTF-8''{$encodedFilename}")
            ->header('Content-Length', (string) $filesize)
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Content-Security-Policy', "default-src 'none'")
            ->send();

        $handle = fopen($filepath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filepath}");
        }

        while (!feof($handle)) {
            $buffer = fread($handle, 8192);
            if ($buffer === false) {
                fclose($handle);
                throw new \RuntimeException("Error reading file: {$filepath}");
            }
            echo $buffer;
            flush();
        }

        fclose($handle);
        exit;
    }

    /**
     * Sends a redirect response.
     *
     * @param string $url The URL to redirect to.
     * @param int $code The HTTP status code.
     * @throws \InvalidArgumentException If the URL is invalid.
     */
    public function redirect(string $url, int $code = 302): void
    {
        // Validate URL
        if (filter_var($url, FILTER_VALIDATE_URL) === false && !str_starts_with($url, '/')) {
            throw new \InvalidArgumentException('Invalid redirect URL');
        }

        // Handle secure redirects
        if ($this->secure && parse_url($url, PHP_URL_SCHEME) === 'http') {
            $url = 'https://' . substr($url, 7);
        }

        // Ensure URL is properly encoded
        $encodedUrl = filter_var($url, FILTER_SANITIZE_URL);

        $this->setStatusCode($code)
            ->header('Location', $encodedUrl)
            ->header('Content-Type', 'text/plain')
            ->setContent('Redirecting...')
            ->send();
        exit;
    }

    /**
     * Redirects the user to the previous page.
     *
     * @param string|null $fallback The URL to redirect to if the referer is not available.
     */
    public function back(?string $fallback = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        $this->redirect($referer);
    }

    /**
     * Sets the response to be non-cacheable.
     *
     * @return $this
     */
    public function noCache(): self
    {
        return $this->header('Cache-Control', 'no-cache, no-store, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Sends a JSON error response.
     *
     * @param string $message The error message.
     * @param int $code The HTTP status code.
     * @return $this
     */
    public function withError(string $message, int $code = 400): self
    {
        return $this->json([
            'error' => true,
            'message' => $message
        ], $code);
    }

    /**
     * Sends a JSON success response.
     *
     * @param mixed $data The data to be included in the response.
     * @param string $message The success message.
     * @return $this
     */
    public function withSuccess(mixed $data = null, string $message = 'Success'): self
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Sends the HTTP response.
     *
     * @throws \RuntimeException If headers have already been sent.
     */
    public function send(): void
    {
        if (headers_sent($file, $line)) {
            throw new \RuntimeException("Headers already sent in {$file} on line {$line}");
        }

        // Send status header
        http_response_code($this->statusCode);

        // Set content type with optional charset
        if (!isset($this->headers['Content-Type'])) {
            $contentType = $this->contentType;
            if ($this->charset !== null && $this->shouldIncludeCharset($contentType)) {
                $contentType .= '; charset=' . $this->charset;
            }
            $this->header('Content-Type', $contentType);
        }

        // Add security headers
        $this->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-XSS-Protection', '1; mode=block');

        // Send headers
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }

        // Send content
        if ($this->content !== null) {
            echo $this->content;
        }
    }

    /**
     * Checks if the content type should include the charset.
     *
     * @param string $contentType The content type.
     * @return bool True if the charset should be included, false otherwise.
     */
    private function shouldIncludeCharset(string $contentType): bool
    {
        $typesWithCharset = [
            'text/',
            'application/json',
            'application/xml',
            'application/javascript'
        ];

        foreach ($typesWithCharset as $type) {
            if (str_starts_with($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Streams the response content using a callback function.
     *
     * @param callable $callback The callback function that generates the content.
     * @param int $bufferSize The buffer size in bytes.
     * @throws \RuntimeException If headers have already been sent.
     */
    public function stream(callable $callback, int $bufferSize = 8192): void
    {
        if (headers_sent($file, $line)) {
            throw new \RuntimeException("Headers already sent in {$file} on line {$line}");
        }

        $this->send();

        if (ob_get_level()) {
            ob_end_flush();
        }

        flush();

        while ($data = $callback($bufferSize)) {
            echo $data;
            flush();
        }
    }

    /**
     * Returns the response as a string.
     *
     * @return string The response as a string.
     */
    public function __toString(): string
    {
        try {
            ob_start();
            $this->send();
            return ob_get_clean() ?: '';
        } catch (\Throwable $e) {
            return 'Error generating response: ' . $e->getMessage();
        }
    }
}