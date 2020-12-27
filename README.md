# t.t.t

A database-free blog engine that reads from a folder of Markdown files.

For more info, see `content/What is this.txt`.

## When should I use it?

Written purely in PHP with most dependencies hard-copied in the codebase, this engine is suitable for restrictive hosting platforms where PHP is the only option and no database or package manager is allowed.

## Deploy

You can try

## Setup

First, install dependencies. Assuming you have [Composer](https://getcomposer.org/doc/01-basic-usage.md) installed somewhere in your `$PATH`, do:

```shell
composer.phar install
```

Then, actually execute the server.

You can run t.t.t via Docker (because, 2020):

```shell
docker build -t ttt .
docker run -p 80:80 --rm --name the-tslimi-tk ttt
```

Alternatively, you can try one of the following methods:

- Locally (macOS): `php -S localhost:9000`.
- Docker Compose: `docker-compose up`.

## Usage

To post a new article, simply upload your `txt` file to `content/`.
