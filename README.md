## Installation & updates

Clone the repository
`git clone https://github.com/StarEngineer89/ci_tournament_bracket-generator` then `composer update`

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> The end of life date for PHP 7.4 was November 28, 2022.
> The end of life date for PHP 8.0 was November 26, 2023.
> If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> The end of life date for PHP 8.1 will be November 25, 2024.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

## Shield Authentication enable

To use the user authentication, run the following command.
`php spark shield:setup`

When prompt a questions to overwrite the existing configurations, select `'n'`.

On the final prompt `Run `spark migrate --all`now? [y, n]:`, select `'y'`.

## Google authentication

1. Set Up Google API Credentials
   First, you need to create a project in the Google Cloud Console and obtain OAuth 2.0 credentials.

- Go to the [Google Cloud Console](https://console.cloud.google.com/?hl=ru).
- Create a new project or select an existing one.
- Navigate to "Credentials" in the sidebar.
- Create credentials > OAuth 2.0 Client ID.
- Configure the consent screen, including adding authorized redirect URIs (e.g., http://localhost:8080/callback).
- Create the client ID and download the client_secret.json file or copy the Client ID and Client Secret.
- Add the credentials into the .env file

  > `GOOGLE_CLIENT_ID = {google_client_id}`

  > `GOOGLE_CLIENT_SECRET = {google_client_secret}`

  > `GOOGLE_REDIRECT_URI = http://localhost:8080/auth/google/callback`

2. Install Google Client Library

- To interact with Google's OAuth 2.0 API, you need to install the Google API PHP client library. You can do this using Composer:
  `composer require google/apiclient:^2.0`

## Create the upload directory

- Create the Uploads Directory
  `mkdir -p /path/to/your/codeigniter/writable/uploads`
- Create the Symbolic Link

  On Windows

  > `mklink /D \"C:\path\to\your\codeigniter\public\uploads\" \"C:\path\to\your\codeigniter\writable\uploads\"`

  On Linux

  > `ln -s /path/to/your/codeigniter/writable/uploads /path/to/your/codeigniter/public/uploads`
