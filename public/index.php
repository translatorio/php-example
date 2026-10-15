<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Locales\Upload;
use Translatorio\Client;
use Translatorio\Exception\ApiError;
use Translatorio\Exception\AuthenticationError;
use Translatorio\Exception\InvalidRequest;
use Translatorio\Exception\QuotaExhausted;
use Translatorio\Exception\RateLimited;
use Translatorio\Exception\ServerError;

$languages = ['es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'it' => 'Italian', 'pt-BR' => 'Portuguese (Brazil)', 'ja' => 'Japanese'];
$key = getenv('TRANSLATORIO_KEY') ?: '';
$posted = $_SERVER['REQUEST_METHOD'] === 'POST';

// The form as it was sent, or as it starts: Spanish, and the sample when no file is chosen.
// Only codes from the list above are ever sent, whatever the form carried.
$targets = $posted ? array_values(array_intersect(array_keys($languages), array_filter((array) ($_POST['targets'] ?? []), 'is_string'))) : ['es'];
$sample = ! $posted || isset($_POST['sample']);
$context = is_string($_POST['context'] ?? null) ? trim($_POST['context']) : '';

$result = null;
$error = $key === '' ? 'TRANSLATORIO_KEY is not set. Start the app with your key in the environment, as the README shows.' : null;
$details = [];

if ($posted && $key !== '') {
    try {
        // Over post_max_size PHP drops the whole form, so an empty form with a body is a file too big.
        if ($_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            throw new InvalidArgumentException(Upload::TOO_BIG);
        }

        if ($targets === []) {
            throw new InvalidArgumentException('Choose at least one language.');
        }

        $file = Upload::take($_FILES['file'] ?? null, $sample, __DIR__ . '/../samples/en.json');

        $tr = Client::create(
            key: $key,
            baseUrl: getenv('TRANSLATORIO_URL') ?: 'https://translatorio.com/api',
        );

        // The bytes go up as they are, with their name: the extension tells the format, and
        // each language comes back named after it (en.json as es.json, app.po as app.es.po).
        $result = $tr->translateFileBytes($file->bytes, $file->name, $targets, array_filter(['context' => $context]));
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (AuthenticationError) {
        $error = 'Translatorio did not accept the key. Check TRANSLATORIO_KEY.';
    } catch (QuotaExhausted $e) {
        $error = 'Not enough credits for this file' . ($e->remaining() === null ? '.' : ': ' . number_format($e->remaining()) . ' left.');
    } catch (InvalidRequest $e) {
        foreach ($e->fields() as $field => $messages) {
            foreach ((array) $messages as $message) {
                $details[] = "{$field}: {$message}";
            }
        }

        $error = 'Translatorio could not take this request' . ($details === [] ? ': ' . $e->getMessage() : '.');
    } catch (RateLimited $e) {
        $error = sprintf('Too many requests for now. Try again in %d second%s.', $e->retryAfter(), $e->retryAfter() === 1 ? '' : 's');
    } catch (ServerError) {
        // Already retried by the client: the network failed, or Translatorio did, every time.
        $error = 'Could not reach Translatorio, or it did not answer. Try again in a minute.';
    } catch (ApiError $e) {
        $error = 'Translatorio refused the request: ' . $e->getMessage();
    }
}

require __DIR__ . '/../views/translator.php';
