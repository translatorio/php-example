<?php
/** @var array<string, string> $languages */
/** @var list<string> $targets */
/** @var bool $sample */
/** @var string $context */
/** @var \Translatorio\FileTranslation|null $result */
/** @var string|null $error */
/** @var list<string> $details */
$e = fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Locale file translator</title>
    <link rel="stylesheet" href="/translator.css">
</head>
<body>
    <h1>Locale file translator</h1>

    <form method="post" action="/" enctype="multipart/form-data">
        <label>File <input type="file" name="file" accept=".json,.po,.pot,.xliff,.xlf,.csv,.xml,.strings,.md,.html,.txt"></label>
        <label class="check"><input type="checkbox" name="sample" value="1" <?= $sample ? 'checked' : '' ?>> Use the sample file (samples/en.json) when no file is chosen</label>
        <fieldset>
            <legend>Into</legend>
            <?php foreach ($languages as $code => $name): ?>
                <label class="check"><input type="checkbox" name="targets[]" value="<?= $e($code) ?>" <?= in_array($code, $targets, true) ? 'checked' : '' ?>> <?= $e($name) ?></label>
            <?php endforeach ?>
        </fieldset>
        <label>Context (optional)
            <textarea name="context" rows="2"><?= $e($context) ?></textarea>
            <small>What the strings are and who reads them, such as "Buttons and labels of a checkout page".</small>
        </label>
        <?php if ($error): ?>
            <div class="error">
                <p><?= $e($error) ?></p>
                <?php if ($details): ?><ul><?php foreach ($details as $detail): ?><li><?= $e($detail) ?></li><?php endforeach ?></ul><?php endif ?>
            </div>
        <?php endif ?>
        <button type="submit">Translate</button>
    </form>

    <?php if ($result): ?>
        <p class="summary">
            Format: <?= $e((string) $result->format()) ?> · Strings: <?= $result->strings() ?> · Credits charged: <?= number_format($result->charged()) ?>
            <?php if ($result->creditsRemaining() !== null): ?> · Remaining: <?= number_format($result->creditsRemaining()) ?><?php endif ?>
        </p>

        <?php if ($result->isTest()): ?>
            <p class="notice">Test key: every translation comes back marked, like [es] Hello, and nothing is charged. Use a live key for real translations.</p>
        <?php endif ?>

        <?php foreach ($result->files() as $file): ?>
            <?php $name = (string) ($file['name'] ?? ''); $content = (string) ($file['content'] ?? ''); ?>
            <article>
                <h2><?= $e($name) ?></h2>
                <?php if (($file['intact'] ?? false) === true): ?>
                    <p class="ok">Intact: everything protected came back.</p>
                <?php else: ?>
                    <p class="warn">A placeholder or tag did not come back. Check it before shipping.</p>
                <?php endif ?>
                <pre><?= $e($content) ?></pre>
                <a class="button" href="data:application/octet-stream;base64,<?= $e(base64_encode($content)) ?>" download="<?= $e($name) ?>">Download <?= $e($name) ?></a>
            </article>
        <?php endforeach ?>
    <?php endif ?>
</body>
</html>
