Configurator & translator
=========================

Installation
------------

```sh
$ composer require geniv/nette-configurator
```
or
```json
"geniv/nette-configurator": "^4.0"
```

require:
```json
"php": ">=7.0",
"nette/nette": ">=2.4",
"dibi/dibi": ">=3.0",
"geniv/nette-locale": ">=2.0",
"geniv/nette-search-content": ">=1.0",
"geniv/nette-translator": ">=2.0"
```

Include in application
----------------------

available source drivers:
- DevNullDriver (default values)
- DibiDriver (dibi + cache `_Configurator-DibiDriver`)

self translator class:
- ConfiguratorTranslator (cache `_Configurator-ConfiguratorTranslator`)

neon configure:
```neon
# configurator
configurator:
#   debugger: true
#   autowired: true
#    driver: Configurator\Drivers\DevNullDriver
    driver: Configurator\Drivers\DibiDriver(%tablePrefix%)
    translator: false
#    searchMask: 
#       - *Translation.neon
    searchPath:
        - %appDir%/presenters/AppTranslation.neon
#    excludePath:
#        - CustomExcludeTranslation.neon
```

neon configure extension:
```neon
extensions:
    configurator: Configurator\Bridges\Nette\Extension
```

### description
internal combination `id_ident` and `id_locale` must by unique! type content is only like "_category_" or "_flag of type_"

Internal data are loading in **first** usage component.

Available interface: `IConfigurator`

usage:
```php
protected function createComponentConfig(IConfigurator $configurator): IConfigurator
{
    // disable auto create ident
    //$configurator->setAutoCreate(false);
    return $configurator;
}
```

```php
$this['config']->isEnable('ident'): bool;
$this['config']->getValue('ident'): mixed;

// echo value of ident
$this['config']->renderText('ident');
// return value of ident
$this['config']->renderText('ident', true);

// substitute logic
$this['config']->renderText('ident', null, 'arg');
$this['config']->renderText('ident', null, ['arg']);

// set data like translator
$this['config']->setTranslator('ident', 'text');
$this['config']->setEditor('ident', 'new text');

$this['config']->getDataByIdent('ident');
```

usage:
```latte
{control config:text 'web-title'}

{control config:text 'web-title', true}

{control config:text 'web-title', null, 'argument'}
{control config:text 'web-title', null, ['argument']}

<h1 n:if="$presenter['config']->isEnable('web-title')">{control config:text 'web-title'}</h1>

<h1 n:if="$presenter['config']->getValue('show-web-title')">{control config:text 'web-title'}</h1>

{* long equivalent - WHY?!: *}
<h1 n:if="$presenter['config']->renderCheckbox('show-web-title', true)">{control config:text 'web-title'}</h1>
```
