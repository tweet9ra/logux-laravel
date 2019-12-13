# Instalation #
1) Add package to your project: `composer require tweet9ra/logux-laravel`
2) Add `tweet9ra\Logux\Laravel\LoguxServiceProvider` to providers list (config/app.php => 'providers')
3) Publish config and routes: `pa vendor:publish --provider="tweet9ra\Logux\Laravel\LoguxServiceProvider"`

Configure `config/logux.php` and `routes/logux.php`
