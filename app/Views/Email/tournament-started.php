<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.magicLinkSubject') ?></title>
</head>

<body>
    <p>Hi <?= esc($username) ?>,</p>
    <p>The <strong><?= $tournament->name ?></strong> (<?= base_url("tournaments/$tournament->id/view") ?>) hosted by <?= $creator->name ?> (<?= $creator->email ?>) has officially started, and you're in the action! Get ready to track the progress. </p>

    🔹 <strong>Your Role</strong>: <?= $role ?>

    <p>Stay engaged, follow the matches, and may the best participant win!</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?>Team</p>
    <br />
    <p>Disclaimer: To opt out of these emails, login and adjust the notification setting from the "bell" icon.</p>
</body>

</html>