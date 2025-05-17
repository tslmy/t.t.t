# ![Logo](public/favicon-16x16.png) **t.t.t**

[![pre-commit](https://img.shields.io/badge/pre--commit-enabled-brightgreen?logo=pre-commit&logoColor=white)](https://github.com/pre-commit/pre-commit)
[![Build Status](https://www.travis-ci.com/tslmy/t.t.t.svg?branch=master)](https://www.travis-ci.com/tslmy/t.t.t)
[![codecov](https://codecov.io/gh/tslmy/t.t.t/branch/master/graph/badge.svg?token=K603JQ63AV)](https://codecov.io/gh/tslmy/t.t.t)
[![HitCount](http://hits.dwyl.com/tslmy/ttt.svg)](http://hits.dwyl.com/tslmy/ttt)

**t.t.t** is a lightweight, database-free blog engine that renders a folder of Markdown files as blog posts.

<p align="center">
  <img src="https://tva1.sinaimg.cn/large/e6c9d24egy1h2r3dr7vyqj20qw0m0jt1.jpg" alt="Screenshot" />
</p>

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

---

## 🚀 Setup

### 🔧 Local

Make sure [Composer](https://getcomposer.org/doc/01-basic-usage.md) is installed and available in your `$PATH`.

```bash
composer install                   # Install dependencies
php -S localhost:9000 -t public   # Start the server
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

Run [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) before committing:

```bash
php-cs-fixer fix
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

## 📝 License

Licensed under **GPL-3.0**. See [`LICENSE`](LICENSE) for details.
