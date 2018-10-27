<?php declare(strict_types=1);

namespace Configurator\Bridges\Nette;

use Configurator\Bridges\Tracy\Panel;
use Configurator\ConfiguratorTranslator;
use Nette\DI\CompilerExtension;


/**
 * Class Extension
 *
 * @author  geniv
 * @package Configurator\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'debugger'    => true,
        'autowired'   => true,
        'driver'      => null,
        'searchMask'  => ['*Translation.neon'],
        'searchPath'  => [],
        'excludePath' => [],
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // define driver
        $default = $builder->addDefinition($this->prefix('default'))
            ->setFactory($config['driver'])
            ->addSetup('setSearchPath', [$config['searchMask'], $config['searchPath'], $config['excludePath']])
            ->setAutowired($config['autowired']);

//TODO omezit podminkou!!!!
        $trans = $builder->addDefinition($this->prefix('translate'))
            ->setFactory(ConfiguratorTranslator::class);

        // linked filter to latte
        $builder->getDefinition('latte.latteFactory')
            ->addSetup('addFilter', ['translate', [$trans, 'translate']]);

        // define panel
        if ($config['debugger']) {
            $panel = $builder->addDefinition($this->prefix('panel'))
                ->setFactory(Panel::class, [$default]);

            // linked panel to tracy
            $builder->getDefinition('tracy.bar')
                ->addSetup('addPanel', [$panel]);
        }
    }
}
