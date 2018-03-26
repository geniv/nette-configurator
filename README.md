Configurator
============

Installation
------------

```sh
$ composer require geniv/nette-configurator
```
or
```json
"geniv/nette-configurator": ">=1.0.0"
```

require:
```json
"php": ">=7.0.0",
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

### description
internal combination `id_ident` and `id_locale` must by unique! type content is only like "_category_"

usage:
```php
protected function createComponentConfig(Configurator $configurator)
{
    // disable auto create ident
    //$configurator->setAutoCreate(false);
    return $configurator;
}
```

```php
// return bool of enabled ident
$this['config']->isEnableText('ident');

// echo value of ident
$this['config']->renderText('ident');

// return value of ident
$this['config']->renderText('ident', true);

// direct return value
$this['config']->getText('ident');

// return value of show-web-title (long equivalent)
$this['config']->renderCheckbox('show-web-title', true);

// set data like translator
$presenter['config']->setTranslator('ident', 'text');
```

usage:
```latte
{control config:text 'web-title'}

{control config:text 'web-title', true}

<h1 n:if="$presenter['config']->isEnableText('web-title')">{control config:text 'web-title'}</h1>

<h1 n:if="$presenter['config']->getCheckbox('show-web-title')">{control config:text 'web-title'}</h1>

{* long equivalent: *}
<h1 n:if="$presenter['config']->renderCheckbox('show-web-title', true)">{control config:text 'web-title'}</h1>
```
