# Balero CMS

New version of Balero CMS.

# Install

Unzip and upload  to your /htdocs/ or your /www folder.

```bash
$ unzip balero-cms.zip -d /www
```

Set write permissions to config file.

```bash
$ chmod +w ./balerocms-src/resources/config/balero.config.json
```

Go to: [http://localhost/balerocms-src/](http://localhost/balerocms-src/) and installer will install for you.

Enjoy. Done!

# Extra configuration for developers or customization

## Update/Install Front-End Libs

Adjust your library version in `package.json` and execute:

```bash
$ npm install
```

It will update your local front-end libraries.

---

## Run Unit Tests

Create your tests in `tests/Framework` and execute:

```bash
$ composer install
$ composer test
```

---

## Using Third-Party Libraries in Balerocms

Balerocms is ready to use Composer for managing third-party libraries. Follow these steps to include and use external libraries in your CMS.

### 1. Install libraries

Navigate to the root of your project (`balerocms-src/`) and run:

```bash
$ composer install
```

### 1. Add a library with Composer


```bash
$ composer require vendor/package-name
```

#### Example: Installing Guzzle for HTTP requests (third-party library)

```bash
$ composer require guzzlehttp/guzzle
```

This will:

- Download the library into `vendor/`.

---

### 2. Include the library in the CMS

Now you can use the library inside a controller. For example:

```php
<?php
namespace Modules\Example\Controllers;

use Framework\Core\Controller;
use GuzzleHttp\Client; // Third-party library

class ExampleController extends Controller
{
    public function fetchData()
    {
        // Create Guzzle HTTP client
        $client = new Client();

        // Send GET request
        $response = $client->get('https://api.example.com/data');

        // Get response body
        $body = $response->getBody()->getContents();

        return $body;
    }
}
```

# Reposiroty

GitHub: [https://github.com/librixsoft/balerocms-src](https://github.com/librixsoft/balerocms-src)

Mirror: [https://balerocms@bitbucket.org/librixsoft/balerocms-src.git
](https://balerocms@bitbucket.org/librixsoft/balerocms-src.git
)
