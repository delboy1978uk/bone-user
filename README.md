# bone-user
Integration of delboy1978uk/user for BoneMvcFramework - WIP
##Usage
Simply add the Package to Bone's module config
```php
<?php

// use statements here
use BoneMvc\Module\BoneMvcUser\BoneMvcUserPackage;
use Del\UserPackage;

return [
    'packages' => [
        // packages here (order is important)...,
        UserPackage::class,
        BoneMvcUserPackage::class,
    ],
    // ...
];
