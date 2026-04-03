<?php

declare(strict_types=1);

use EmmanuelAutin\CodeIgniter4Vite\Config\Vite;

if (! function_exists('vite_is_running')) {
    function vite_is_running(): bool
    {
        $config = config(Vite::class);

        return is_file($config->hotFile);
    }
}

if (! function_exists('vite_dev_server')) {
    function vite_dev_server(): ?string
    {
        $config = config(Vite::class);

        if (! is_file($config->hotFile)) {
            return null;
        }

        $content = trim((string) file_get_contents($config->hotFile));

        return $content !== '' ? rtrim($content, '/') : null;
    }
}

if (! function_exists('vite_manifest')) {
    function vite_manifest(): array
    {
        static $manifest = null;

        if ($manifest !== null) {
            return $manifest;
        }

        $config = config(Vite::class);

        if (! is_file($config->manifestPath)) {
            throw new RuntimeException('Vite manifest not found: ' . $config->manifestPath);
        }

        $decoded = json_decode(
            (string) file_get_contents($config->manifestPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid Vite manifest format.');
        }

        $manifest = $decoded;

        return $manifest;
    }
}

if (! function_exists('vite_asset_url')) {
    function vite_asset_url(string $path): string
    {
        $config = config(Vite::class);

        return base_url(rtrim($config->assetBasePath, '/') . '/' . ltrim($path, '/'));
    }
}

if (! function_exists('vite_imported_chunks')) {
    function vite_imported_chunks(array $manifest, array $chunk): array
    {
        $imports = [];
        $seen = [];

        $walk = function (array $currentChunk) use (&$walk, &$imports, &$seen, $manifest): void {
            if (empty($currentChunk['imports']) || ! is_array($currentChunk['imports'])) {
                return;
            }

            foreach ($currentChunk['imports'] as $importName) {
                if (isset($seen[$importName])) {
                    continue;
                }

                $seen[$importName] = true;

                if (! isset($manifest[$importName])) {
                    continue;
                }

                $importChunk = $manifest[$importName];
                $imports[] = $importChunk;

                $walk($importChunk);
            }
        };

        $walk($chunk);

        return $imports;
    }
}

if (! function_exists('vite_tags')) {
    function vite_tags(array|string $entries): string
    {
        $entries = (array) $entries;
        $html = [];

        if (vite_is_running()) {
            $devServer = vite_dev_server();

            if ($devServer === null) {
                throw new RuntimeException('Invalid Vite hot file content.');
            }

            $html[] = '<script type="module" src="' . esc($devServer . '/@vite/client') . '"></script>';

            foreach ($entries as $entry) {
                $html[] = '<script type="module" src="' . esc($devServer . '/' . ltrim($entry, '/')) . '"></script>';
            }

            return implode("\n", $html);
        }

        $manifest = vite_manifest();
        $seenCss = [];
        $seenImports = [];

        foreach ($entries as $entry) {
            if (! isset($manifest[$entry])) {
                throw new RuntimeException("Vite entry not found in manifest: {$entry}");
            }

            $chunk = $manifest[$entry];
            $imports = vite_imported_chunks($manifest, $chunk);

            foreach ($imports as $importChunk) {
                if (! empty($importChunk['file']) && ! isset($seenImports[$importChunk['file']])) {
                    $seenImports[$importChunk['file']] = true;
                    $html[] = '<link rel="modulepreload" href="' . esc(vite_asset_url($importChunk['file'])) . '">';
                }
            }

            foreach ($imports as $importChunk) {
                if (! empty($importChunk['css']) && is_array($importChunk['css'])) {
                    foreach ($importChunk['css'] as $cssFile) {
                        if (! isset($seenCss[$cssFile])) {
                            $seenCss[$cssFile] = true;
                            $html[] = '<link rel="stylesheet" href="' . esc(vite_asset_url($cssFile)) . '">';
                        }
                    }
                }
            }

            if (! empty($chunk['css']) && is_array($chunk['css'])) {
                foreach ($chunk['css'] as $cssFile) {
                    if (! isset($seenCss[$cssFile])) {
                        $seenCss[$cssFile] = true;
                        $html[] = '<link rel="stylesheet" href="' . esc(vite_asset_url($cssFile)) . '">';
                    }
                }
            }

            if (empty($chunk['file'])) {
                throw new RuntimeException("Missing built file for Vite entry: {$entry}");
            }

            $html[] = '<script type="module" src="' . esc(vite_asset_url($chunk['file'])) . '"></script>';
        }

        return implode("\n", $html);
    }
}
