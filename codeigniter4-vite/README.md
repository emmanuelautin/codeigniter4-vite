# codeigniter4-vite

Small Vite integration helper for CodeIgniter 4.

## Features

- Loads assets from Vite dev server in development
- Loads built assets from `public/build/manifest.json` in production
- Supports CSS and modulepreload tags
- Designed to work with `vite-plugin-codeigniter4`

## Installation

```bash
composer require emmanuelautin/codeigniter4-vite
```

## Optional config override

Create `app/Config/Vite.php` in your project:

```php
<?php

namespace Config;

class Vite extends \EmmanuelAutin\CodeIgniter4Vite\Config\Vite
{
}
```

## Usage in a view

```php
<?= vite_tags([
    'resources/css/app.css',
    'resources/js/app.js',
]) ?>
```

## Dev mode

When `writable/vite/hot` exists, assets are served from the Vite development server.

## Production mode

Assets are resolved using `public/build/manifest.json`.
