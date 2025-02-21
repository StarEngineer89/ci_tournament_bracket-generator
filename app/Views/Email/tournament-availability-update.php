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
    <p>The availability of tournament <strong><?= $tournament->name ?></strong> (<?= base_url("tournaments/$tournament->id/view") ?>) hosted by <?= $creator->username ?> (<?= $creator->email ?>) has been updated to the following new START/END Date/Time.</p>

    🔹 <strong>Duration</strong>: <?= $startTime ?> - <?= $endTime ?>

    <p>If you have any questions about these changes, please reach out to the tournament organizer.</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?>Team</p>
    <br />
    <p>Disclaimer: To opt out of these emails, login and adjust the notification setting from the "bell" icon.</p>
</body>

</html>