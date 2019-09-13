# bone-user
Integration of delboy1978uk/user for BoneMvcFramework - WIP
##Usage
Simply add the Package to Bone's module config
```php
<?php

// use statements here
use BoneMvc\Module\BoneMvcUser\BoneMvcUserPackage;

return [
    'packages' => [
        // packages here...,
        BoneMvc\Module\BoneMvcUser\BoneMvcUserPackage::class,
    ],
    // ...
];
