# bone-user
[![Latest Stable Version](https://poser.pugx.org/delboy1978uk/bone-user/v/stable)](https://packagist.org/packages/delboy1978uk/bone-user) [![Build Status](https://travis-ci.com/delboy1978uk/bone-user.png?branch=master)](https://travis-ci.com/delboy1978uk/bone-user) [![Code Coverage](https://scrutinizer-ci.com/g/delboy1978uk/bone-user/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/delboy1978uk/bone-user/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/delboy1978uk/bone-user/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/delboy1978uk/bone-user/?branch=master) [![License](https://poser.pugx.org/delboy1978uk/bone-user/license)](https://packagist.org/packages/delboy1978uk/bone-user)

User registration and login package for Bone Framework
## setup
Simply add the Package to Bone's module config
```php
<?php

// use statements here
use Bone\BoneDoctrine\BoneDoctrinePackage;
use Bone\Mail\MailPackage;
use Bone\Paseto\PasetoPackage;
use Bone\User\BoneUserPackage;
use Del\Person\PersonPackage;
use Del\UserPackage;

return [
    'packages' => [
        // packages here (order is important)...,
        MailPackage::class,
        BoneDoctrinePackage::class,
        PasetoPackage::class,
        PersonPackage::class,
        UserPackage::class,
        BoneUserPackage::class,
    ],
    // ...
];
```
And once you have done that, setup the DB using the migration commands:
```
vendor/bin/bone migrant:diff
vendor/bin/bone migrant:migrate
vendor/bin/bone migrant:generate-proxies
```
Quick point of order here. If you always type `vendor/bin/whatever`, save yourself the hassle by adding the following to
your `~/.bashrc` or `~.zshrc` or whichever shell file:
```php
export PATH=$PATH:bin:vendor/bin
```
Then you can close and reopen your terminal, and from now on you can just type `bone`. The same for any 
executable stored in a `bin/` directory in your current folder.

The last setup stage is to deploy the public assets (CSS, JS, etc.)
```
vendor/bin/bone assets:deploy
```
(You added your PATH so didn't have to type `vendor/bin/bone`, right?)
## usage
Once installed head to your site (by default using the Bone Framework Docker development environment (it's 
`https://awesome.scot/user`) and register yourself as a user. Again if using the provided dev environment, check your 
MailHog server at `https://awesome.scot:8025` to see any outgoing mails in a convenient web inbox. Activate your account
etc! You can see all of the available endpoints in `src/BoneUserPackage.php` in the `addRoutes()` section.
### authorization middleware
You can lock down a route to make it available to a logged in user by adding the session authorization middleware
`Bone\User\Http\Middleware\SessionAuth` like so:
```php
$sessionAuth = $c->get(SessionAuth::class); // of course there's a use statement above, right? With the full name?
$router->map('GET', '/my/route', [MyController::class, 'whateverAction'])->middleware($sessionAuth);
```
Once a route is locked down, in your controller you can get a User object by calling:
```php
$user = $request->getAttribute('user');
```
There is also a `SessionAuthRedirect` middleware class, which you can add and which will take the visitor to the login 
page, but redirect back there once logged in.
### service and repository classes
You can also fetch the `Del\Service\UserService` from your packages container regsistration class to inject into your 
classes. This allows you to perform various funcions, most cases of which are mostly covered, but in practical terms 
allows you to access the database repository and save data. A user object also has a `Del\Entity\Person` ando you can 
also fetch the `Del\Person\Service\PersonService` from the container. 
```php
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
   
    /** @var PersonService $personService */
    $personService = $c->get(PersonService::class);
``` 
### views
Obviously, you probably won't want the default Bone view with the pirate theme! So head into your config
folder and override the views. Email templates are also in there. Hack away.
```php
<?php

/*
 *  You can override views from vendor packages
 *  You should copy the vendor view folder into your src and tweak from there
 */
return [
    'views' => [
        'boneuser' => 'src/App/View/bone-user',
    ],
];
````
### config settings
If you'd like to go to your own controller upon login, create a config with the key `bone-user`, and add the URL you 
wish to redirect to. You can also disable the `/user/register` end point, and you can make filling in a profile a 
requirement.
```php
<?php /* config/bone-user.php */

return [
    'bone-user' => [
        'loginRedirectRoute' => '/admin',
        'enableRegistration' => true,
        'requireProfile' => false,
        'rememberMeCookie' => true
    ],
];
````

That's about it! The rest should autocomplete in your IDE, and it's all pretty straightforward! Have fun.
