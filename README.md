# t.t.t

A database-free blog engine that reads from a folder of Markdown files.

For more info, see `content/What is this.txt`.

## When should I use it?

Written purely in PHP with most dependencies hard-copied in the codebase, this engine is suitable for restrictive hosting platforms where PHP is the only option and no database or package manager is allowed.

## Setup

You can run t.t.t via Docker (because, 2020):

```shell
docker build -t ttt .
docker run -p 80:80 --rm --name the-tslimi-tk ttt
```

Alternatively, you can try one of the following methods:

- Manual setup: Simply upload all files to your server.
- Locally (macOS): `php -S localhost:9000`.
- Deploy to [Heroku](https://heroku.com/deploy).
- Docker Compose: `docker-compose up`.

## Usage

To post a new article, simply upload your `txt` file to `content/`.
