# t.t.t

A database-free blog engine that reads from a folder of Markdown files.

For more info, see `content/What is this.txt`.

## When should I use it?

Written purely in PHP with most dependencies hard-copied in the codebase, this engine is suitable for restrictive hosting platforms where PHP is the only option and no database or package manager is allowed.

## Setup

Simply upload all files to your server.

Alternatively, you can run t.t.t via Docker (because, 2020):

```shell
docker build -t ttt .
docker run -p 80:80 --rm --name the-tslimi-tk ttt
```

To try it out locally, on macOS, you can simply do `php -S localhost:9000`.

## Usage

To post a new article, simply upload your `txt` file to `content/`.