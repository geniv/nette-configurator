Configurator
============

Installation
------------

```sh
$ composer require geniv/nette-configurator
```
or
```json
"geniv/nette-configurator": ">=1.0"
```

require:
```json
"php": ">=5.6.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0.0"
```

Include in application
----------------------

neon configure:
```neon
services:
    - Configurator(%tablePrefix%)
```

usage:
```php
use Configurator;

protected function createComponentConfig(Configurator $configurator)
{
    // disable auto create ident
    //$configurator->setAutoCreate(false);
    return $configurator;
}
```

```php
$this['config']->isEnableText('ident');

$this['config']->renderText('ident');

$this['config']->renderText('ident', true);
```

usage:
```latte
{control config:text 'web-title'}

{control config:text 'web-title', true}

<h1 n:if="$presenter['config']->isEnableText('web-title')">{control config:text 'web-title'}</h1>
```
