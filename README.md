# ![logo](public/favicon-16x16.png) t.t.t

[![Build Status](https://www.travis-ci.com/tslmy/t.t.t.svg?branch=master)](https://www.travis-ci.com/tslmy/t.t.t)

A database-free blog engine that reads from a folder of Markdown files.

![demo](https://imgur.com/Ei5ZgaA.jpg)

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

## Setup

### Locally
Assuming you have [Composer](https://getcomposer.org/doc/01-basic-usage.md) installed somewhere in your `$PATH`, do:

```shell
composer.phar install # install dependencies
php -S localhost:9000 -t public  # actually start the server
```

### Via Docker

You can run t.t.t via Dockerfile:

```shell
docker build -t ttt .
docker run -p 80:80 --rm --name ttt-demo ttt
```

... or via Docker Compose: `docker-compose up`.

### Via Kubernetes

I will be using [minikube](https://minikube.sigs.k8s.io/) in this walkthrough. I will be using the local Docker Registry as the source of the Kubenetes image.

```shell
# Start the cluster:
minikube start
# Register the Docker Registry to minikube -- This is because we will be building the image from the Dockerfile for Kubenetes:
eval $(minikube docker-env)
# Build the Docker image for Kubenetes:
docker build -t ttt .
# Apply the Deployment (which manages the Pods/"virtual hosts" in the minikube cluster for the app) as well as the Service (which is a Load Balancer in this case that exposes the web app in the Pods) using the manifest file:
kubectl apply -f kubernetes-manifest.yml
# Access the web app:
minikube service ttt-demo-service
```

## Usage

To post a new article, simply upload your `txt` file to `public/content/`.

To organize posts into categories, simply create subdirectories under `public/content/` and put `txt` files there. Nested directories are accepted.

To change the favicon, replace these files:

- android-chrome-192x192.png
- android-chrome-512x512.png
- apple-touch-icon.png
- favicon-16x16.png
- favicon-32x32.png
- favicon.ico
- site.webmanifest

## FAQ

- When should I use it?
  This engine is great when you want to publish a folder of `txt` files as a blog real quick.
- Why the name "t.t.t"?
  It was an acronym for my old blog, `the.tslimi.tk`. Apparently, it has lost this origin as I moved on to other blogging platforms. Feel free to interpret it any way you like.
- Why PHP?
  This project was developed during an era where cPanel-based free web-hosting were popular. These web-hosting providers usually only allow PHP as the only dynamic web language on their platforms, hence the choice.

## Changelog

This project was originally written more than a decade ago while I was still a middle schooler. I didn't update this repo until recently (Dec 2020), during which time I removed/modified a number of features:

- Instead of hard-wiring all the dependencies within the repo, t.t.t currently exploits CDNs and [package managers](https://getcomposer.org) for importing libraries.
- The "quick access" list has been removed. Instead, use your favorite search engine.
- The `_intro.txt` behavior has been removed to reduce complexity in the rendering process.
- The caching behavior has been removed. It was a fun experience to have implemented my own caching mechanism, but the complexity-efficiency trade-off was just not paying off.
- Instead of writing my own CSS, I'm now using the [mvp.css](https://andybrewer.github.io/mvp/) template. This switch keeps the HTML in this repo more semantic and the CSS more up to modern standards.
- Instead of using one `favicon.ico` file, the blog engine is now using a whole set of favicon files generated from [favicon.io](https://favicon.io/).

## License

GPL-3.0. See `LICENSE`.