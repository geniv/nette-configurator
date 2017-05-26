# nette-configurator
======

Configurator component

"geniv/nette-configurator": ">=1.0"

services:
	- Configurator(%tb_configurator%)

use Configurator;

protected function createComponentConfig(Configurator $configurator)
 {
     return $configurator;
 }


{control config:text 'web-title'}

<h1 n:if="$presenter['config']->isEnableText('web-title')">{control config:text 'web-title'}</h1>
