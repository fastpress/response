# Fastpress\Http\Response
A part of the Fastpress framework, **Fastpress\Http\Response** is a PHP class designed for handling HTTP responses efficiently and effectively. It offers a comprehensive set of features for managing response codes, headers, body content, and more, making it an indispensable tool for any PHP web application.

## Features

- **Response Code Management**: Easily set HTTP response codes and corresponding texts.
- **Response Body Handling**: Define and manage the body content of responses.
- **Headers Management**: Add and manipulate response headers.
- **Redirection Support**: Simple methods to redirect users to different URLs.
- **Caching Controls**: Methods to control and disable browser caching.
- **Protocol Specification**: Customize the HTTP protocol version.
- **Content Rendering**: Render the response content and headers efficiently.
- **Page Refresh**: Facilitate immediate page refreshes.

## Installation

Use Composer to integrate Fastpress\Http\Response into your project:

```bash
composer require fastpress/response
```
## Requirements
- PHP 7.4 or higher.

## Usage
**Setting Response Code and Text**
```php
use Fastpress\Http\Response;

$response = new Response();
$response->setResponse(200, 'OK');
```
## Methods

### `setContent(mixed $content): self`

Sets the content of the response.

**Parameters:**

- `$content`: The content to send in the response.

**Returns:**

- The `Response` instance.


### `setContentType(string $contentType, ?string $charset = null): self`

Sets the content type and optional charset of the response.

**Parameters:**

- `$contentType`: The content type (e.g., 'text/html', 'application/json').
- `$charset`: The charset (e.g., 'UTF-8').

**Returns:**

- The `Response` instance.


### `setStatusCode(int $code): self`

Sets the HTTP status code of the response.

**Parameters:**

- `$code`: The HTTP status code (e.g., 200, 404, 500).

**Returns:**

- The `Response` instance.


### `header(string $name, string $value): self`

Adds a header to the response.

**Parameters:**

- `$name`: The header name.
- `$value`: The header value.

**Returns:**

- The `Response` instance.


### `json(mixed $data, int $status = 200): self`

Sends a JSON response.

**Parameters:**

- `$data`: The data to encode as JSON.
- `$status`: The HTTP status code.

**Returns:**

- The `Response` instance.


### `download(string $filepath, ?string $filename = null): void`

Triggers a file download.

**Parameters:**

- `$filepath`: The path to the file.
- `$filename`: The filename to suggest to the browser.

**Returns:**

- `void`


### `redirect(string $url, int $code = 302): void`

Redirects to a given URL.

**Parameters:**

- `$url`: The URL to redirect to.
- `$code`: The HTTP status code (301, 302, etc.).

**Returns:**

- `void`


### `back(?string $fallback = '/'): void`

Redirects to the referring URL or a fallback URL.

**Parameters:**

- `$fallback`: The fallback URL if no referrer is available.

**Returns:**

- `void`


### `noCache(): self`

Adds headers to prevent caching of the response.

**Returns:**

- The `Response` instance.


### `withError(string $message, int $code = 400): self`

Sends a JSON error response.

**Parameters:**

- `$message`: The error message.
- `$code`: The HTTP status code.

**Returns:**

- The `Response` instance.


### `withSuccess(mixed $data = null, string $message = 'Success'): self`

Sends a JSON success response.

**Parameters:**

- `$data`: The data to include in the response.
- `$message`: The success message.

**Returns:**

- The `Response` instance.


### `send(): void`

Sends the HTTP response.

**Returns:**

- `void`


### `stream(callable $callback, int $bufferSize = 8192): void`

Streams the response content.

**Parameters:**

- `$callback`: A callable that generates the response content in chunks.
- `$bufferSize`: The buffer size for each chunk.

**Returns:**

- `void`