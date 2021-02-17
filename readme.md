# Billing

A general purpose interface to Stripe that's optimized for Laravel 5 SaaS applications.

### Support

`Billing` is an MIT-licensed open source project. If this saved you some time, please consider donating via [Patreon](https://patreon.com/tylercubell).

### Installation

1. Run `composer require tylercubell/billing`.
2. Run `php artisan migrate`.
3. Add `tylercubell\Billing\Providers\BillingServiceProvider:class` to your application's service providers in `config/app.php`.
4. Add `tylercubell\Billing\Facades\Billing:class` to your application's class aliases in `config/app.php`.
5. Add `use tylercubell\Billing\Traits\UserTrait;` as a trait in your application's user model.
6. Add `use tylercubell\Billing\Exceptions\BillingException;` to your controllers.
7. Run `php artisan vendor:publish --tag=billing`
8. Set your application's Stripe secret API token in `config/services.php`.
9. Add a webhook in your Stripe account to `{your_url}/billing/webhook`.
10. Sync Stripe data with `php artisan billing:sync`.

*Optional*: for applications with existing users and/or an existing Stripe account, run `php artisan billing:link` after syncing to link your synced Stripe customers to your users based on their email address.

*Optional*: for new Stripe accounts, you can define plans and coupons in `config/billing.php` and run `php artisan billing:bootstrap` to create them in your Stripe account.

### Usage

`Billing` was created with three goals in mind:

1. Reduce Stripe API calls and application load times.
2. Avoid writing unnecessary billing code.
3. Reduce dependency from the Stripe API and gain ownership of your data.

In order to do this, `Billing` syncs your Stripe data with your application's database, then automically updates your local data whenever your application initates an create/update/delete action or Stripe sends data to your webhook.

The code is self-documentating and tries to follow the Stripe API as closely as possible. Take a look at the `src/Traits` directory for information on how to use each API resource.

For example, here's how you would create an application user and a Stripe customer at the same time:


```php
try {
    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => bcrypt($data['password'])
    ]);

    $customer = Billing::createCustomer([
        'user_id'     => $user->id,
        'description' => $user->name,
        'email'       => $user->email
    ]);
} catch (BillingException $e) {
    // Catch all exception for all Stripe exceptions
}
```

`Billing` automatically creates your customer in Stripe and syncs the result back to your application's database. When you're ready to retrieve your customer's information, use `Billing::retrieveCustomer($customerId)` or `Auth::user()->customer()` via the `UserTrait` and `Billing` will query your database instead of calling the Stripe API. In the rare event that the Stripe API goes down, your application will still be able to function in a read-only mode.


### Helpers

- Validator extensions for checking validity of plans and coupons.
- Middleware for checking if a user can take certain actions like adding cards or coupons.
- Extra model attributes that help with formatting currency and dates, coupon terms, etc. See `src/Models` for more information.
- Trait for your user model to retrieve your user's card or subscription.
- Events that tell your application when something is created/updated/deleted via a webhook or an action taken by your application.

### Notes

- The goal of `Billing` is to syncronize only SaaS-related API resources. Orders, returns, SKUs, etc., will not be supported.
- Some object attributes are not included like `three_d_secure` and anything that has to do with the balance object.
- Models automatically return all objects in a list so `has_more` is always `false`.
- Some of the "list all" methods can't sort objects by date since Stripe doens't offer a date field for some objects.
- Not all code paths are thoroughly tested. Customers, subscriptions, discounts, plans, coupons, invoices, and charges should work but the rest haven't been tested yet.

### Contributing

Any help would be greatly appreciated, especially with testing and writing documentation.