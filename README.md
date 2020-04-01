# README #

<a href="https://travis-ci.org/PKeidel/banktolaravel"><img src="https://travis-ci.org/PKeidel/banktolaravel.svg" alt="Build Status"></a>

## What it is
This package create a database table to store all bank bookings/statements. Also a artisan command for importing them directly from your bank is created with `php artisan bank:import`.
New Entries are published as an event you can easily listen to, see [Usage](#Usage) for more.
This package supports HBCI/FinTS, a german standard for communicating with bank institutes.

## Thank you
A ton of thanks to nemiah/fints-hbci-php for providing and maintaining such a nice library where I could easily put my work on top of it!

## Install

First add the composer dependency:
```shell
composer require pkeidel/banktolaravel
```

Then publish and run the migration to create the 'bookings' table:
```php
php artisan vendor:publish --provider="PKeidel\BankToLaravel\Providers\BankToLaravelServiceProvider" --tag=migrations
php artisan migrate
```

Now add this to a good protected route group:
```php
Route::resource('bookings', '\PKeidel\BankToLaravel\Controllers\BookingsController');
```


Finally create a task scheduler as described [HERE](https://laravel.com/docs/5.5/scheduling#defining-schedules):
```php
$schedule->command('bank:import')->hourly();
```

## Usage

Append these values to your .env file:
```shell
# https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
# The page is in german, so: bank account=Bankleitzahl
FHP_BANK_START="7 days ago"
FHP_BANK_URL=
FHP_BANK_CODE=
FHP_BANK_ACCOUNT=
FHP_ONLINE_REGISTRATIONNO=
FHP_ONLINE_BANKING_USERNAME=
FHP_ONLINE_BANKING_PIN=
```

Every time a new Entry is added to the database, an `PKeidel\BankToLaravel\Events\NewEntry` Event is fired.

In some ServiceProvides `boot()` function you could simply listen to the events and add some own logic like sending an E-Mail or notify you via some other way.

```php
Event::listen('PKeidel\BankToLaravel\Events\Error', function (Error $error) {
    Log::error("BankToLaravel error: $error->exception");
});

Event::listen('PKeidel\BankToLaravel\Events\NewEntry', function (NewEntry $entry) {
    optional(Users::where('iban', $entry->data['ref_iban'])->first())->notify(new NewBankaccountBooking($entry));
});
```
