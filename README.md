# BladeZero

Import Laravel's Blade templating engines into non-Laravel packages **the right way**.


### The wrong way

#### Dependency Hell
All other standalone versions of blade require ~16 dependencies, and run the `Iluminate/Container` and `Illuminate/View` packages in your app.
 
Apart from the issues ([caused by puling in `Illuminate\Support` in framework-agnostic packages](https://mattallan.org/posts/dont-use-illuminate-support/)), this also adds a ton of overhead to your app.

#### Just not good enough
There are a few instances of packages rewriting the entire Blade engine from scratch.

This creates the following typical issues:

* No longer able to rely on the [Laravel docs](https://laravel.com/docs/6.x/blade) means documentation is often incorrect.
* Features are often missing or implemented incorrectly.


### The \*right\* way

BladeZero is a direct split from `laravel/framework` and only includes files directly needed to compile Blade templates - no Container, no View, no fuss.

95% of the code is a perfect match for the code written by Taylor Otwell and the Laravel contributors meaning every single Blade feature is supported exactly as it should be.


## Installation

Use [Composer](https://getcomposer.org/):

```bash
composer require jakewhiteley/bladezero
```

## Differences
Even though the Blade compiler is 100% the same as it's Laravel twin, your application is not. 

Because of this, BladeZero provides methods to easily 


### csrf

Use the `setCsrfHandler` method to specify how to provide the `@csrf()` directive with the correct token:

```php
$blade->setCsrfHandler(function(): string {
    return MySessionClass::getUserToken();
});
```

### auth
```php
$blade->setAuthHandler(function(string $guard = null): bool {
    return MyUserClass::currentUserIs($guard);
});
```

### can
```php
$blade->setcanHandler(function($abilities, $arguments = []): bool {
    return MyUserClass::currentUserCan($abilities, $arguments);
});
```

### inject
```php
$blade->setInjectHandler(function(string $service) {
    return MyContainer::resolveService($service);
});
```

### error

Should return `string|false`

```php
$blade->setErrorHandler(function(string $key) {
    return MyErrorBag::getErrorMessageFor($key);
});
```




 ### Illuminate\View\Factory
 It's missing! 