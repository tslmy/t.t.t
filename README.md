# ![Logo](public/favicon-16x16.png) **t.t.t**

[![Build](https://github.com/tslmy/t.t.t/actions/workflows/build.yml/badge.svg)](https://github.com/tslmy/t.t.t/actions/workflows/build.yml)
[![pre-commit](https://img.shields.io/badge/pre--commit-enabled-brightgreen?logo=pre-commit&logoColor=white)](https://github.com/pre-commit/pre-commit)
[![codecov](https://codecov.io/gh/tslmy/t.t.t/branch/main/graph/badge.svg?token=K603JQ63AV)](https://codecov.io/gh/tslmy/t.t.t)

**t.t.t** is a lightweight, database-free blog engine that renders a folder of Markdown files as blog posts.

<p align="center">
  <img src="https://tva1.sinaimg.cn/large/e6c9d24egy1h2r3dr7vyqj20qw0m0jt1.jpg" alt="Screenshot" width="400px" />
</p>

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

---

## 🚀 Setup

### 🔧 Local

Requires PHP 8.2+ and [Composer](https://getcomposer.org/doc/01-basic-usage.md) available in your `$PATH`.

```bash
composer install                   # Install dependencies
php -S localhost:9000 -t public public/index.php   # Start the server (index.php is the router)
```

### 🐳 Docker

Set an environment variable `$PATH_TO_NOTES` to the folder containing your blog posts.

**Using Dockerfile:**

```bash
# Build the Docker image
docker build -t ttt .

# Run the container
docker run -p 80:80 --rm --name ttt-demo \
  -v $PATH_TO_NOTES:/var/www/html/public/content:ro ttt
```

**Using Docker Compose:**

```bash
docker-compose up
```

### ☸️ Kubernetes (via Minikube)

```bash
# Start the cluster
minikube start

# Point Docker CLI to Minikube’s Docker daemon
eval $(minikube docker-env)

# Build the image
docker build -t ttt .

# Apply Kubernetes manifests
kubectl apply -f kubernetes-manifest.yml

# Access the app
minikube service ttt-demo-service
```

---

## ⚙️ Configuration

All settings are optional environment variables (for Docker, add `-e NAME=value`; `docker-compose` passes them through from your shell).

| Variable | Default | Meaning |
|---|---|---|
| `TTT_SITE_NAME` | `My blog` | Site title |
| `TTT_SITE_DESC` | `Powered by t.t.t, …` | Tagline shown on the index |
| `TTT_ITEMS_PER_PAGE` | `10` | Posts per index page |
| `TTT_PREVIEW_BYTES` | `1000` | How much of each post the index previews |
| `TTT_CONTENT_DIR` | `content` | Posts folder, relative to `public/` |
| `TTT_SITE_URL` | _(guessed from the request)_ | Your site's origin, e.g. `https://example.com` — used for absolute links in the feed, sitemap and `canonical` tags. Set it in production. |
| `TTT_DISQUS_SHORTNAME` | _(empty)_ | Set to enable Disqus comments |
| `TTT_ANALYTICS_DOMAIN` | _(empty)_ | Set to enable a [Plausible](https://plausible.io)-style analytics script for this domain |
| `TTT_ANALYTICS_SCRIPT_URL` | `https://plausible.io/js/script.js` | Point at a self-hosted Plausible/Umami-compatible script |

CSS ([mvp.css](https://andybrewer.github.io/mvp/)) and syntax highlighting ([highlight.js](https://highlightjs.org/)) are vendored in `public/vendor/`, so the site makes no third-party requests unless you enable comments or analytics.

---

## 🔗 URLs

| URL | Shows |
|---|---|
| `/` | Newest posts (`?page=2` for more) |
| `/folder/essays/2024` | Newest posts under `public/content/essays/2024/` |
| `/post/essays/2024/title` | One post (`title.md` or `title.txt`) |
| `/feed.xml` | [Atom](https://en.wikipedia.org/wiki/Atom_(web_standard)) feed of the 20 newest posts (advertised in every page's `<head>`) |
| `/sitemap.xml` | Every post, for search engines |

Old links (`view.php?name=…`, `index.php?folder=…`) are redirected permanently. Clean URLs need the rewrite rules in `public/.htaccess` (Apache with `mod_rewrite` and `AllowOverride All`; the Docker image and Heroku are already set up). For nginx, send any request that isn't a real file to `index.php`. The site also works in a sub-directory such as `/blog`.

Link to another post from Markdown with `~essays/title.md~` or `~essays/title.md:link text~`.

---

## ✍️ Usage

- **To publish a post:** Upload your `.txt` or `.md` file to `public/content/`.
- **To categorize posts:** Create subdirectories under `public/content/` and place files inside. Nested directories are supported.
- **To change the favicon:** Replace the following files:

  ```
  android-chrome-192x192.png
  android-chrome-512x512.png
  apple-touch-icon.png
  favicon-16x16.png
  favicon-32x32.png
  favicon.ico
  site.webmanifest
  ```

---

## ❓ FAQ

- **When should I use this?**
  When you want to quickly publish a blog from a folder of `.txt` or `.md` files with minimal setup.

- **Why the name “t.t.t”?**
  Originally short for `the.tslimi.tk`, my old blog. You're welcome to interpret it however you like now.

- **Why PHP?**
  Back in the day of cPanel-based free hosting, PHP was often the only server-side language supported — so, PHP it was.

---

## 🛠 Development

Run the checks before committing (CI runs the first two):

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix
```

---

## 📜 Changelog

This project was first built when I was in middle school. In the Dec 2020 revamp, the following changes were made:

- Switched to using CDNs and [Composer](https://getcomposer.org) for dependencies. (No, it had not dependency manager back then.)
- Removed the “quick access” list — use your favorite search engine instead.
- Removed `_intro.txt` support for rendering simplicity.
- Dropped the custom caching logic to reduce complexity.
- Replaced hand-rolled CSS with [mvp.css](https://andybrewer.github.io/mvp/) for cleaner HTML and modern styling.
- Adopted a full favicon set generated via [favicon.io](https://favicon.io/).

On Apr 21, 2025, I had ChatGPT rewrite this whole README file.

---


## 📊 Code Statistics

_t.t.t_ is written with ~200 lines of PHP with just 4 dependencies. With page templates totaling ~130 lines of [Twig](https://twig.symfony.com/) and CSS shamelessly delegated to [mvp.css](https://andybrewer.github.io/mvp/), _t.t.t_ is less than 400 lines of code in total.

Language|files|blank|comment|code
:-------|-------:|-------:|-------:|-------:
PHP|4|28|24|213
Twig|3|1|0|127
Markdown|1|48|0|102
YAML|5|4|4|95
XML|1|1|0|25**
JSON|2|0|0|24
Dockerfile|1|3|0|15
Text|1|4|0|5
Properties|1|3|7|2
--------|--------|--------|--------|--------
SUM:|19|92|35|608

---

## 📝 License

Licensed under **GPL-3.0**. See [`LICENSE`](LICENSE) for details.
