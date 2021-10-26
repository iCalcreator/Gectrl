[comment]: # (This file is part of Gectrl, PHP Genereric controller. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)

# Gectrl

## About

Gectrl is a PHP generic controller class package

* Supports the MVC software design pattern
* Distinguish controller and application logic using a strategy pattern
  
The controller provides coordination logic

The controller delegates application logic to actionClasses

* using implementations of the (strategy) [ActionClassInterface],
* invoking of actionClass condition **evaluate** and opt, logic **doAction** methods,
* passing all data information in an encapsulated [Package] class instance
  * input, output, config, logger etc


## Usage

#### Basic

Simpler (http/html) example

``` php
<?php
namespace Kigkonsult\Gectrl;
use ActionSrc\PrepAction;
use ActionSrc\CreateAction;
use ActionSrc\ReadAction;
use ActionSrc\UpdateAction;
use ActionSrc\DeleteAction;
use ActionSrc\CatchUpAction;
require 'vendor/autoload.php';

...

$package = Gectrl::init( $config, $logger )
    ->setActionClasses(
        [
            PrepAction::class,
            CreateAction::class,
            ReadAction::class,
            UpdateAction::class,
            DeleteAction::class,
            CatchUpAction::class,
        ]
    )
    ->main( $_REQUEST );
...

echo $package->getOutput();
```

For more detailed usage, read [Gectrl], [ActionClassInterface] and [Package] docs. 

## Installation

[Composer], from the Command Line:

```
composer require kigkonsult/gectrl
```

In your composer.json:

``` json
{
    "require": {
        "kigkonsult/gectrl": "dev-master"
    }
}
```

Version 1.6 supports PHP 7.4, 1.4 7.0.

## Sponsorship
Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, [e-mail]</a>.

## Licence

Gectrl is licensed under the LGPLv3 License.

[ActionClassInterface]:docs/ActionClassInterface.md
[Composer]:https://getcomposer.org/
[e-mail]:mailto:ical@kigkonsult.se
[Gectrl]:docs/Gectrl.md
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[Package]:docs/Package.md
