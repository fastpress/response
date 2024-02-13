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

**Setting Response Body**
```php
$response->setBody('Your response content here');
```

**Adding Headers**
```php
$response->addHeader('Content-Type', 'application/json');
```

**Redirecting**
```php
$response->redirect('https://your-redirect-url.com', 301);
```

**Disabling Browser Cache**
```php
$response->disableBrowserCache();
```


## Contributing
Contributions are welcome! Please feel free to submit a pull request or open issues to improve the library.


## License
This library is open-sourced software licensed under the MIT license.

## Support
If you encounter any issues or have questions, please file them in the issues section on GitHub.


