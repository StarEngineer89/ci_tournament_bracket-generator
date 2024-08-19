<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <?= $this->renderSection('pageStyles') ?>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="bg-light">

    <div class="header p-2 border-bottom sticky-top bg-light">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-md navbar-light bg-body-tertiary">
                <a class="navbar-brand me-5" href="<?= base_url() ?>">Logo</a>

                <button class="navbar-toggler order-sm-5" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?= base_url() ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('gallery') ?>">Tournament Gallery</a>
                        </li>
                    </ul>
                </div>

            </nav>
        </div>
    </div>
    <main role="main">
        <?= $this->renderSection('main') ?>
    </main>

    <div class="footer border-top p-3">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>Pages</h4>
                    <ul class="link-group">
                        <li><a href="/">Home</a></li>
                        <li><a href="/">About</a></li>
                        <li><a href="<?= base_url('gallery') ?>">Tournament Gallery</a></li>
                        <li><a href="<?= base_url('tournaments') ?>">My Tournament Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                </div>
                <div class="col-md-4">
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copy border-top p-3">
        <div class="container text-center">
            copyright ©️ 2024
        </div>
    </div>
    
    <!-- Cookie Consent Modal -->
    <div id="cookieConsentModal" style="display:none; position:fixed; bottom:0; width:100%; background-color:#f1f1f1; padding:10px; text-align:center;">
        <p>This site uses cookies 🍪 to store information for the purpose of enhancing user experience. <br> If you reject cookies, you may experience limitations with functionality.</p>
        <button onclick="acceptCookies()">Accept</button>
        <button onclick="rejectCookies()">Reject</button>
    </div>

    <script>
        
        // Show the modal if cookie consent is not given
        if (!document.cookie.includes('cookie_consent')) {
            document.getElementById('cookieConsentModal').style.display = 'block';
        }
        setCookie = (name, value, days) => {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        let acceptCookies = () => {
            setCookie('cookie_consent', 'accepted', 365);
            document.getElementById('cookieConsentModal').style.display = 'none';
        }

        let rejectCookies = () => {
            setCookie('cookie_consent', 'rejected', 365);
            document.getElementById('cookieConsentModal').style.display = 'none';
            alert('Cookies rejected. To reactivate, clear your browser history and visit the site again.');
        }
    </script>
    <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
<?= $this->renderSection('pageScripts') ?>
</body>
</html>
