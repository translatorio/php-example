<?php

declare(strict_types=1);

namespace Locales;

use InvalidArgumentException;

/**
 * The file to translate: the one uploaded, or the bundled sample.
 *
 * Read into memory and never written anywhere: the translations are shown on the page that
 * answers the form, and nothing of the file is kept after it.
 */
final class Upload
{
    public const MAX_BYTES = 1024 * 1024;

    public const TOO_BIG = 'The file is over 1 MB. Send a smaller one.';

    /**
     * @param string $name The file's own name, which tells Translatorio its format.
     * @param string $bytes The file exactly as it arrived.
     */
    private function __construct(public readonly string $name, public readonly string $bytes)
    {
    }

    /**
     * @param mixed $file The `file` entry of `$_FILES`.
     * @param bool $sample Whether to send the sample when no file was chosen.
     * @param string $samplePath
     * @return self
     * @throws InvalidArgumentException With a sentence for the page when there is nothing to send.
     */
    public static function take(mixed $file, bool $sample, string $samplePath): self
    {
        $error = is_array($file) && is_int($file['error'] ?? null) ? $file['error'] : UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            if (! $sample) {
                throw new InvalidArgumentException('Choose a file, or tick "Use the sample file".');
            }

            return new self(basename($samplePath), (string) file_get_contents($samplePath));
        }

        // Checked here, before anything is sent, so a file too large never costs a call.
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || $file['size'] > self::MAX_BYTES) {
            throw new InvalidArgumentException(self::TOO_BIG);
        }

        if ($error !== UPLOAD_ERR_OK || ! is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('The file did not arrive. Try again.');
        }

        return new self(basename((string) $file['name']), (string) file_get_contents($file['tmp_name']));
    }
}
