<?php

namespace EmmanuelAutin\CodeIgniter4Vite\Config;

use CodeIgniter\Config\BaseConfig;

class Vite extends BaseConfig
{
    public string $hotFile = WRITEPATH . 'vite/hot';

    public string $buildDirectory = 'build';

    public string $manifestPath = FCPATH . 'build/manifest.json';

    public string $assetBasePath = 'build/';
}
