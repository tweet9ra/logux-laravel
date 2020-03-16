# Logux Laravel #
This package allows your Laravel application communicate with front-end via WebSockets through Logux proxy server
## Quick start ##
1. Install via composer `composer require tweet9ra/logux-laravel`
2. Add `tweet9ra\Logux\Laravel\LoguxServiceProvider` to providers list in your app config file `config/app.php`
```php
...
'providers' => [
    ...
     /*
     * Package Service Providers...
     */
    tweet9ra\Logux\Laravel\LoguxServiceProvider::class,
    ...
],
...
```
3. Publish config and routes: `php artisan vendor:publish --provider="tweet9ra\Logux\Laravel\LoguxServiceProvider"`

Configure `config/logux.php` and `routes/logux.php`
