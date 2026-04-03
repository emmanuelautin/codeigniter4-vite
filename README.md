# CodeIgniter 4 + Vite

Une intégration simple de Vite dans un projet CodeIgniter 4, avec :

* le package npm **`vite-plugin-codeigniter4`**
* le package Composer **`emmanuelautin/codeigniter4-vite`**

L’objectif est d’obtenir un workflow proche de Laravel :

* **dev** : assets servis par le serveur Vite
* **prod** : assets buildés dans `public/build` avec `manifest.json`
* **PHP** : génération automatique des balises `<script>` / `<link>` avec `vite_tags()`

---

## Ce que fait chaque package

### `vite-plugin-codeigniter4`

Le plugin npm s’occupe de la partie **Vite** :

* configuration du build
* support du mode dev
* génération des assets pour la prod
* intégration pensée pour fonctionner avec CodeIgniter 4

### `emmanuelautin/codeigniter4-vite`

Le package Composer s’occupe de la partie **PHP / CodeIgniter 4** :

* détection du mode dev via `writable/vite/hot`
* lecture du `manifest.json` en production
* génération des balises HTML via `vite_tags()`

---

# Installation complète depuis zéro

## 1) Créer un projet CodeIgniter 4

Depuis votre terminal :

```bash
composer create-project codeigniter4/appstarter ci4-vite-demo
cd ci4-vite-demo
```

Ensuite, copiez le fichier d’environnement :

```bash
cp env .env
```

> Sous Windows PowerShell :

```powershell
copy env .env
```

Vous pouvez ensuite lancer le serveur local de CodeIgniter :

```bash
php spark serve
```

Par défaut, votre projet sera accessible sur une URL locale du type :

```txt
http://localhost:8080
```

---

## 2) Installer le package Composer

Depuis la racine du projet :

```bash
composer require emmanuelautin/codeigniter4-vite
```

Ce package expose un helper `vite_tags()` et une configuration `Vite`.

---

## 3) Installer Vite + le plugin npm

Toujours à la racine du projet :

```bash
npm init -y
npm install -D vite vite-plugin-codeigniter4
```

---

## 4) Créer l’arborescence front

Créez la structure suivante :

```txt
ci4-vite-demo/
├─ app/
├─ public/
├─ writable/
├─ resources/
│  ├─ css/
│  │  └─ app.css
│  └─ js/
│     └─ app.js
├─ vite.config.js
├─ package.json
└─ composer.json
```

---

## 5) Ajouter des fichiers front minimaux

### `resources/css/app.css`

```css
body {
    font-family: Arial, sans-serif;
    margin: 40px;
    background: #f7f8fb;
    color: #1f2937;
}

.card {
    max-width: 720px;
    padding: 24px;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    background: #2563eb;
    color: #fff;
    font-size: 12px;
    margin-bottom: 12px;
}
```

### `resources/js/app.js`

```js
import '../css/app.css';

console.log('Vite + CodeIgniter 4 is running');
```

---

## 6) Configurer Vite

Créez un fichier `vite.config.js` à la racine du projet.

### Exemple complet

```js
import { defineConfig } from 'vite';
import codeigniter4 from 'vite-plugin-codeigniter4';

export default defineConfig({
    plugins: [
        codeigniter4(),
    ],
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
            },
        },
    },
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
    },
});
```

### Pourquoi cette config ?

* `outDir: 'public/build'` permet au package PHP de lire les fichiers buildés depuis `public/build`
* `manifest: 'manifest.json'` force la génération du manifest exactement à l’emplacement attendu par le package Composer
* `input.app = 'resources/js/app.js'` définit le point d’entrée principal

---

## 7) Ajouter les scripts npm

Dans `package.json`, ajoutez :

```json
{
  "private": true,
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "vite": "^7.0.0",
    "vite-plugin-codeigniter4": "^1.0.0"
  }
}
```

> Adaptez les versions selon celles réellement publiées.

---

## 8) Charger les assets dans une vue CodeIgniter

Le package Composer fournit un helper `vite_tags()`.

Créez par exemple `app/Views/home.php` :

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CI4 + Vite</title>
    <?= vite_tags('resources/js/app.js') ?>
</head>
<body>
    <div class="card">
        <span class="badge">CodeIgniter 4 + Vite</span>
        <h1>Intégration OK</h1>
        <p>
            Si vous voyez cette page stylée et que la console affiche
            <strong>Vite + CodeIgniter 4 is running</strong>, l’intégration fonctionne.
        </p>
    </div>
</body>
</html>
```

Ensuite, ajoutez un contrôleur simple.

### `app/Controllers/Home.php`

```php
<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home');
    }
}
```

Et une route dans `app/Config/Routes.php` :

```php
$routes->get('/', 'Home::index');
```

---

## 9) Lancer le projet en développement

Dans un premier terminal :

```bash
php spark serve
```

Dans un second terminal :

```bash
npm run dev
```

Ensuite, ouvrez l’application dans votre navigateur :

```txt
http://localhost:8080
```

### Ce qui se passe en dev

Quand le fichier `writable/vite/hot` existe, le helper PHP considère que Vite tourne en mode développement.
Dans ce cas, `vite_tags()` génère les balises pointant vers le serveur Vite, y compris `@vite/client`.

---

## 10) Build de production

Quand vous voulez préparer la production :

```bash
npm run build
```

Cela doit générer un dossier :

```txt
public/build/
├─ manifest.json
└─ assets/...
```

Ensuite, si le hot file n’existe plus, `vite_tags()` lit automatiquement `public/build/manifest.json` et génère :

* les `<link rel="stylesheet">`
* les `<link rel="modulepreload">`
* les `<script type="module">`

---

# Configuration optionnelle côté CodeIgniter

Si vous voulez surcharger la config par défaut, créez :

## `app/Config/Vite.php`

```php
<?php

namespace Config;

class Vite extends \EmmanuelAutin\CodeIgniter4Vite\Config\Vite
{
    public string $hotFile = WRITEPATH . 'vite/hot';
    public string $buildDirectory = 'build';
    public string $manifestPath = FCPATH . 'build/manifest.json';
    public string $assetBasePath = 'build/';
}
```

Dans la majorité des cas, la config par défaut suffit déjà.

---

# Exemple final minimal

## `vite.config.js`

```js
import { defineConfig } from 'vite';
import codeigniter4 from 'vite-plugin-codeigniter4';

export default defineConfig({
    plugins: [codeigniter4()],
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
            },
        },
    },
});
```

## `app/Views/layout.php`

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon app</title>
    <?= vite_tags('resources/js/app.js') ?>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
```

---

# Commandes récapitulatives

```bash
composer create-project codeigniter4/appstarter ci4-vite-demo
cd ci4-vite-demo
cp env .env
composer require emmanuelautin/codeigniter4-vite
npm init -y
npm install -D vite vite-plugin-codeigniter4
php spark serve
npm run dev
```

Pour la prod :

```bash
npm run build
```

---

# Dépannage

## `Vite manifest not found`

Vérifiez que le build a bien généré :

```txt
public/build/manifest.json
```

## `Vite entry not found in manifest: resources/js/app.js`

Vérifiez que le chemin passé à `vite_tags()` correspond exactement à l’entrée déclarée dans `rollupOptions.input`.

## Les styles ne chargent pas

Assurez-vous que votre JS importe bien votre CSS :

```js
import '../css/app.css';
```

## Le mode dev ne bascule pas correctement

Le package PHP se base sur le fichier :

```txt
writable/vite/hot
```

Le plugin npm doit donc écrire ce fichier en mode dev.

---

# README court pour le dépôt

````md
# CodeIgniter 4 Vite integration

Use `vite-plugin-codeigniter4` on the Vite side and `emmanuelautin/codeigniter4-vite` on the PHP side.

## Install

```bash
composer require emmanuelautin/codeigniter4-vite
npm install -D vite vite-plugin-codeigniter4
````

## vite.config.js

```js
import { defineConfig } from 'vite';
import codeigniter4 from 'vite-plugin-codeigniter4';

export default defineConfig({
    plugins: [codeigniter4()],
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
            },
        },
    },
});
```

## In your view

```php
<?= vite_tags('resources/js/app.js') ?>
```

## Development

```bash
npm run dev
php spark serve
```

## Production

```bash
npm run build
```

```

---

# Note

Cette documentation est volontairement orientée **cas réel / démarrage rapide** : installation d’un projet CI4 neuf, setup des deux packages, exemple de vue, exemple d’entrée Vite, et exécution en dev puis en prod.

```
