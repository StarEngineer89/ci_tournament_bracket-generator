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
    <p><strong><?= $tournament->name ?></strong> (<?= base_url("tournaments/$tournament->id/view") ?>) has been shared with you! Get ready to join the action and experience the competition. !</p>

    <?php $user = auth()->user() ? auth()->getProvider()->findById(auth()->user()->id) : null; ?>
    🔹 <strong>Shared By</strong>: <?= $user ? "$user->username ($user->email)" : "Guest User" ?><br />
    🔹 <strong>Your Role</strong>: <?= $role ?>

    <p>You may view, vote, and/or participate depending on the permissions granted.</p>

    <p>If you weren’t expecting this invitation, feel free to ignore it.</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?>Team</p>
    <br />
    <p>Disclaimer: To opt out of these emails, login and adjust the notification setting from the "bell" icon.</p>
</body>

</html>