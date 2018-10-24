<?php declare(strict_types=1);

namespace Translator\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Translator\Bridges\Tracy\Panel;


/**
 * Class Extension
 *
 * @author  geniv
 * @package Translator\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'debugger'    => true,
        'autowired'   => true,
        'driver'      => null,
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
            ->addSetup('setSearchPath', [$config['searchPath'], $config['excludePath']])
            ->setAutowired($config['autowired']);

        // linked filter to latte
        $builder->getDefinition('latte.latteFactory')
            ->addSetup('addFilter', ['translate', [$default, 'translate']]);

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
