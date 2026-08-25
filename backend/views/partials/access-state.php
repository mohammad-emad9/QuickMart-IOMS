<?php
/** @var int $statusCode */
/** @var string $title */
/** @var string $message */
/** @var string $actionHref */
/** @var string $actionLabel */
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS access state">
    <title>QuickMart IOMS - <?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <link rel="stylesheet" href="../../assets/common.css">
</head>
<body class="qm-access-body">
    <main class="qm-access-page" aria-labelledby="accessStateTitle">
        <section class="qm-access-card" role="alert">
            <span class="qm-access-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3 2.8 20h18.4L12 3Z" />
                    <path d="M12 9v5M12 17h.01" />
                </svg>
            </span>
            <p class="qm-access-code" dir="ltr">HTTP <?= (int) $statusCode ?></p>
            <h1 id="accessStateTitle"><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <a class="qm-access-action" href="<?= htmlspecialchars($actionHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <?= htmlspecialchars($actionLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </a>
        </section>
    </main>
</body>
</html>
