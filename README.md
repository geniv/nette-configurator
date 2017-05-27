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

internal dependency:
```json
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0"
```

Include in application
----------------------

neon configure:
```neon
services:
    - Configurator(%tb_configurator%)
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

usage:
```latte
{control config:text 'web-title'}

<h1 n:if="$presenter['config']->isEnableText('web-title')">{control config:text 'web-title'}</h1>
```
