<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.magicLinkSubject') ?></title>
</head>

<body>
    <div class="logo" style="text-align: center;">
        <img src="<?= base_url('images/logo.jpg') ?>" style="max-height: 120px;">
    </div>
    <p>Hi, <?= esc($username) ?>,</p>
    <p>We want to let you know that your <?= esc($sendername) ?> account email has been updated.</p>
    <p>📧 <b>Old Email:</b> <?= esc($oldEmail) ?> </p>
    <p>📧 <b>New Email:</b> <?= esc($newEmail) ?> </p>
    <p>If you made this change, no further action is needed.</p><br />
    <p>🔹 <b>Didn’t request this change?</b></p>
    <p>If you didn’t authorize this update, please log in to your account and change your email immediately from your profile settings</p>
    <a href="<?= url_to('login') ?>" style="color: #ffffff; font-size: 16px; font-family: Helvetica, Arial, sans-serif; text-decoration: none; border-radius: 6px; line-height: 20px; display: inline-block; font-weight: normal; white-space: nowrap; background-color: #0d6efd; padding: 8px 12px; border: 1px solid #0d6efd;"><?= lang('Auth.login') ?></a>
    <br />
    <p><?= esc($sendername) ?> Team</p>
</body>

</html>