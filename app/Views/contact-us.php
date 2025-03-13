<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid align-middle p-5">
    <div class="row mt-3">
        <h3 class="text-center">Join the TournCreator Community!</h3>
        <h5 class="text-center mb-4">We believe tournaments should be fun, easy, and accessible to everyone.</h5>
        <p class="text-center mb-5">Whether you're a casual organizer or a spectator, TournCreator equips you with everything you need to engage with exciting, well-managed competitions!</p>

        <div class="col-12">
            <h5><strong>Key Features:</strong></h5>
            <div class="ps-2">
                <p><strong>Flexible Tournament Formats</strong> – Choose from a variety of formats, including knockout brackets, single elimination, and double elimination.</p>
                <p><strong>Complete Customization</strong> – Personalize every aspect of your tournament: set participant images, customize themes (from Classic to Championship Gold), and even integrate opening media (audio/video) or winner celebration sounds.</p>
                <p><strong>Effortless Sharing & Collaboration</strong> – Share your tournament through the generated QR Code or via a direct link showcased in the <a href="<?= base_url('gallery?filter=glr') ?>">Tournament Gallery</a>. Manage access and permissions from your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> with ease.</p>
                <p><strong>Delegate Administration</strong> – Assign admin (edit) role to other registered users, allowing them to update the tournament description, edit round names, add or remove participants, and manage winners. Collaboration has never been smoother!</p>
                <p><strong>Interactive Voting Options</strong> – Vote/let spectators vote for participants with flexible settings like <strong>Round Duration</strong>, <strong>Open-Ended</strong> Voting, and <strong>Max Votes Per Round</strong>.</p>
                <p><strong>Real-Time Tracking & Engagement</strong> – Keep participants engaged with real-time updates, match progress tracking, and a dynamic <a href="<?= base_url('participants') ?>">leaderboard</a> to enhance the competitive experience.</p>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <h3 class="text-center">Customization at Your Fingertips</h3>
        <p class="text-center"><strong>Shape your tournament the way you want it!</strong></p>
        <div class="col-12">
            <p class="ps-2">✔ Personalize the tournament name, description, and media for a unique identity.</p>
            <p class="ps-2">✔ Add, remove, and customize participants effortlessly.</p>
            <p class="ps-2">✔ Enable public or private voting to determine winners.</p>
            <p class="ps-2">✔ Monitor every tournament action with a detailed log in your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> – perfect for tracking changes when delegating admin (edit) roles.</p>
        </div>
    </div>

    <div class="row mt-5">
        <h3 class="text-center">Frequently Asked Questions (FAQ)</h3>

        <div class="col-12">
            <p><strong>Why is there an authentication mechanism if tournaments can be created for free without signing up?</strong></p>
            <p class="ps-2">Great question! While you can create and administer tournaments for free without an account, signing up unlocks the full power of your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> — giving you more control and flexibility.</p>
        </div>

        <div class="col-12 mt-2">
            <p><strong>With an account, you can:</strong></p>
            <p class="ps-2">✔ Rename your tournament and update its status (In Progress, Completed, Abandoned).</p>
            <p class="ps-2">✔ Archive, reset, or delete tournaments.</p>
            <p class="ps-2">✔ Manage sharing permissions and access detailed logs.</p>
            <p class="ps-2">✔ Customize tournament settings like Name, Description, Elimination Type, Voting Mechanism, Scoring Rules, Participant Images, Audio for Final Winner, and more!</p>
            <p class="ps-2">✔ Registered users can be invited as official participants using the <strong>@username</strong> prefix, ensuring accurate tracking on the <a href="<?= base_url('participants') ?>">Participant Leaderboard</a>.</p>

            <p class="ms-1">Unlike anonymous participants, registered participants retain their stats across multiple tournaments, allowing for better tracking and recognition. If an anonymous participant joins multiple tournaments with the same name, there's no way to verify if they’re the same person. <br />Signing up ensures consistency, proper verification, and priority placement on the <a href="<?= base_url('participants') ?>">Participant Leaderboard</a>!</p>
        </div>

        <div class="col-12 mt-2">
            <p><strong>Can I continue creating tournaments for free without signing up?</strong></p>
            <p class="ps-2">Absolutely! You can create and manage tournaments as a guest with <strong>no restrictions</strong> — except for one:</p>
            <p class="ps-2">To prevent spam, guest-created tournaments are <strong></strong>automatically deleted after 24 hours</strong>.</p>
            <p class="ps-2">If you’d like to keep your tournament beyond this period, simply <a href="<?= base_url('register') ?>">Sign up</a> or <a href="<?= base_url('login') ?>">Log in</a> to claim and preserve it!</p>
        </div>

        <div class="col-12 mt-2">
            <p><strong>What happens if I accidentally leave my browser session before signing up/signing in?</strong></p>
            <p class="ps-2">Unfortunately, guest tournaments are tied to the current browser session. If you close the tab and youre using your browser as something like incognito/private mode, your tournament will be unclaimed and automatically <strong>deleted</strong> after 24 hours!</p>
            <p class="ps-2">If you want to retain full access, we recommend signing up/signing in before leaving your session. That way, your tournament is linked to your account and won’t be lost!</p>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h3 class="text-center">Need Help? We're Here for You!</h3>
            <p><strong>Have questions or need assistance?</strong></p>
            <p>Contact us at <a href="mailto:contact@tourncreator.com">contact@tourncreator.com</a>, and we’ll be happy to help!</p>
            <p>Please make sure you provide as much details as possible to ensure your request is clear for us.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>