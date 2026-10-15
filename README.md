![Translatorio PHP example](https://raw.githubusercontent.com/translatorio/php-example/main/art/banner.png)

# Translatorio example: plain PHP, no framework

A locale file translator built with [Translatorio](https://translatorio.com), plain PHP, no framework and the PHP SDK ([translatorio/php-sdk](https://github.com/translatorio/php-sdk)). Upload a translation file (JSON, PO, XLIFF, CSV, Android XML, iOS `.strings`, Markdown, HTML or plain text), tick the languages, and one file per language comes back in the same shape:

- **the same keys**, with only the strings translated;
- **the same placeholders**: `:name`, `{count}`, `%s` and `%d` come back exactly as they were, and so do links;
- **a check per file**: if anything protected did not come back, the page says so before you ship it.

Each file is shown and can be downloaded as it is. The app keeps nothing: the file is read, sent with the SDK and answered on the same page. A sample, [`samples/en.json`](samples/en.json), is there to try it without a file of your own.

The walkthrough is on the blog: [translatorio.com/blog/translate-locale-files-in-plain-php](https://translatorio.com/blog/translate-locale-files-in-plain-php).

## Run it

```bash
composer install
TRANSLATORIO_KEY=tr_test_... php -S localhost:8000 -t public
```

The translation is in [`public/index.php`](public/index.php). Requires PHP 8.1 with `ext-curl`.

## What you need

- A Translatorio key from your [organization's keys](https://translatorio.com/organization/keys).
  The free plan is enough: 2,000 credits a month. A `tr_test_` key is free and never reaches
  the model: its translations come back marked, like `[es] Hello`. Use a live key to see real
  translations.

The key goes in the environment as `TRANSLATORIO_KEY`. `TRANSLATORIO_URL` is optional and
points the app at another address of the API (`https://translatorio.com/api` when unset).

## The other examples

The same app with each client: [plain PHP](https://github.com/translatorio/php-example), [Laravel](https://github.com/translatorio/laravel-example), [Express](https://github.com/translatorio/express-example) and [Flask](https://github.com/translatorio/flask-example).

## Author

Created by [Edu Lazaro](https://edulazaro.com) for [Translatorio](https://translatorio.com), the translation API it shows.

## License

This example is open-sourced software licensed under the [MIT license](LICENSE).
