# ![Logo](public/favicon-16x16.png) **t.t.t**

[![Build](https://github.com/tslmy/t.t.t/actions/workflows/build.yml/badge.svg)](https://github.com/tslmy/t.t.t/actions/workflows/build.yml)
[![pre-commit](https://img.shields.io/badge/pre--commit-enabled-brightgreen?logo=pre-commit&logoColor=white)](https://github.com/pre-commit/pre-commit)
[![codecov](https://codecov.io/gh/tslmy/t.t.t/branch/main/graph/badge.svg?token=K603JQ63AV)](https://codecov.io/gh/tslmy/t.t.t)

**t.t.t** is a small, database-free blog engine. Point it at a folder of Markdown files and it serves them as a blog: no build step, no admin panel, no database.

<p align="center">
  <img src="https://tva1.sinaimg.cn/large/e6c9d24egy1h2r3dr7vyqj20qw0m0jt1.jpg" alt="Screenshot" width="400px" />
</p>

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/tslmy/t.t.t)

## ✨ Features

- **Just files.** Drop `.md` or `.txt` files into a folder; subfolders become categories.
- **GitHub-flavored Markdown**, with smart quotes and dashes, and syntax highlighting for code blocks. Raw HTML in posts is stripped.
- **Optional YAML header** for a title, date, summary, or draft flag.
- **Internal links** with `~post~` or `~post:label~`.
- **Paginated index**, breadcrumbs, an Atom feed, a sitemap, and canonical/Open Graph tags.
- **Optional comments** ([giscus](https://giscus.app)) and **analytics** (Plausible/Umami-style), both off by default.
- **No third-party requests** unless you turn on comments or analytics: CSS and JS are vendored.
- **Configured by environment variables** only.

## 🚀 Setup

### 🔧 Local

Requires PHP 8.4+ with `mbstring`, and [Composer](https://getcomposer.org/doc/01-basic-usage.md).

```bash
composer install                  # Install dependencies
php -S localhost:9000 -t public   # Start the server
```

Then open <http://localhost:9000>. A sample post lives in `public/content/`.

### 🐳 Docker

Set `$PATH_TO_NOTES` to the folder containing your posts.

**Dockerfile:**

```bash
docker build -t ttt .

docker run -p 80:80 --rm --name ttt-demo \
  -v $PATH_TO_NOTES:/var/www/html/public/content:ro ttt
```

**Docker Compose:**

```bash
docker-compose up
```

### ☁️ Render

Click **Deploy to Render** above, or create a Blueprint from this repo. [`render.yaml`](render.yaml) builds the `Dockerfile` and sets `PORT=80`. Add more `TTT_*` settings under `envVars` or in the Render dashboard.

### ☸️ Kubernetes (via Minikube)

```bash
minikube start
eval $(minikube docker-env)           # Use Minikube's Docker daemon
docker build -t ttt .
kubectl apply -f kubernetes-manifest.yml
minikube service ttt-demo-service
```

## ✍️ Writing posts

- **Publish:** put a `.md` or `.txt` file in `public/content/`.
- **Categorize:** put files in subfolders. Nesting is fine.
- **Set a title, date or summary** (all optional) with a YAML header:

  ```markdown
  ---
  title: Hello, world
  date: 2024-05-01 09:30     # defaults to the file's modification time
  summary: Shown on the index instead of the start of the post (Markdown).
  draft: true                # hides the post until you remove this line
  ---
  Post text…
  ```

  Without a `title`, the file name is used. A header that can't be parsed is ignored and shown as ordinary text, so a typo never hides a post.
- **Link to another post:** `~folder/other-post~` or `~folder/other-post:link text~`. Your `~~strikethrough~~` still works.
- **Change the favicon:** replace the files `android-chrome-192x192.png`, `android-chrome-512x512.png`, `apple-touch-icon.png`, `favicon-16x16.png`, `favicon-32x32.png`, `favicon.ico` and `site.webmanifest` in `public/`.

## ⚙️ Configuration

All settings are optional environment variables. For Docker, add `-e NAME=value`; `docker-compose` passes them through from your shell.

| Variable | Default | Meaning |
|---|---|---|
| `TTT_SITE_NAME` | `My blog` | Site title |
| `TTT_SITE_DESC` | `Powered by t.t.t, a database-free blog engine.` | Tagline shown on the index |
| `TTT_SITE_URL` | _(guessed from the request)_ | Your site's origin, e.g. `https://example.com`. Used for absolute links in the feed, sitemap and `canonical` tags. **Set it in production.** |
| `TTT_ITEMS_PER_PAGE` | `10` | Posts per index page |
| `TTT_PREVIEW_BYTES` | `1000` | How much of each post the index previews |
| `TTT_CONTENT_DIR` | `content` | Posts folder, relative to `public/` |
| `TTT_SHOW_DRAFTS` | _(empty)_ | Set to anything to also show posts marked `draft: true` (handy locally) |
| `TTT_GISCUS_REPO`, `TTT_GISCUS_REPO_ID`, `TTT_GISCUS_CATEGORY`, `TTT_GISCUS_CATEGORY_ID` | _(empty)_ | Set all four to enable comments |
| `TTT_ANALYTICS_DOMAIN` | _(empty)_ | Set to enable an analytics script for this domain |
| `TTT_ANALYTICS_SCRIPT_URL` | `https://plausible.io/js/script.js` | Point at a self-hosted Plausible/Umami-compatible script |

### 💬 Comments

Comments are powered by [giscus](https://giscus.app): each post's comments are a GitHub Discussion in a repository you choose, so there's no extra account, no ads and no tracking.

1. Install the [giscus app](https://github.com/apps/giscus) on a repository with Discussions turned on.
2. On [giscus.app](https://giscus.app), enter the repository and a category.
3. Copy `data-repo`, `data-repo-id`, `data-category` and `data-category-id` from the generated snippet into the four `TTT_GISCUS_*` variables.

Comments are matched to posts by URL path, so renaming a post starts a new thread. (`TTT_DISQUS_SHORTNAME` is no longer used; existing Disqus comments are not migrated.)

### 📰 Feed and sitemap

- `feed.php` is an [Atom](https://en.wikipedia.org/wiki/Atom_(web_standard)) feed of the 20 newest posts. Every page advertises it in its `<head>`.
- `sitemap.php` lists every post for search engines.

Both build absolute URLs, so set `TTT_SITE_URL` in production. Without it, the address is guessed from the request's `Host` header.

## 🛠 Development

```bash
composer install
vendor/bin/phpunit                # tests (CI)
vendor/bin/phpstan analyse        # static analysis (CI)
vendor/bin/php-cs-fixer fix       # code style
```

CI runs the tests on PHP 8.4 and 8.5 and builds the Docker image.

## 🗂 Project layout

```
public/       Web root: index.php, view.php, feed.php, sitemap.php, vendored CSS/JS, content/
src/          commons.php: configuration and all helper functions
templates/    Twig page templates
tests/        PHPUnit tests
```

## 📊 Code statistics

About 700 lines of PHP and 135 lines of [Twig](https://twig.symfony.com/), with 5 runtime dependencies: [league/commonmark](https://commonmark.thephpleague.com/), [michelf/php-smartypants](https://github.com/michelf/php-smartypants), [pagerfanta](https://github.com/BabDev/Pagerfanta), [symfony/yaml](https://symfony.com/doc/current/components/yaml.html) and Twig. Styling is delegated to [mvp.css](https://andybrewer.github.io/mvp/). Tests, vendored assets and config files are not counted.

## ❓ FAQ

- **When should I use this?**
  When you want to publish a blog from a folder of `.txt` or `.md` files with minimal setup.

- **Why the name “t.t.t”?**
  Originally short for `the.tslimi.tk`, my old blog. Interpret it however you like.

- **Why PHP?**
  Back in the days of cPanel-based free hosting, PHP was often the only server-side language available. So, PHP it was.

## 📜 History

This project was first built when I was in middle school. Since then:

- **Dec 2020:** switched to [Composer](https://getcomposer.org) for dependencies (there was no dependency manager back then); removed the “quick access” list, `_intro.txt` support and the custom caching; replaced hand-rolled CSS with [mvp.css](https://andybrewer.github.io/mvp/); adopted a favicon set from [favicon.io](https://favicon.io/).
- **Recently:** configuration moved to environment variables; CDN assets are now vendored; Disqus was replaced by giscus; added YAML headers, drafts, an Atom feed, a sitemap and SEO tags; moved the demo from Heroku to Render; requires PHP 8.4+.

## 📝 License

Licensed under **GPL-3.0**. See [`LICENSE`](LICENSE) for details.
