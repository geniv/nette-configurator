<?php declare(strict_types=1);

namespace Configurator;

use Locale\ILocale;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Translator\Translator;


/**
 * Class ConfiguratorTranslator
 *
 * @author  geniv
 * @package Configurator
 */
class ConfiguratorTranslator extends Translator
{
    /** @var IConfigurator */
    private $configurator;
    /** @var Cache */
    private $cache;


    /**
     * ConfiguratorDriver constructor.
     *
     * @param ILocale       $locale
     * @param IConfigurator $configurator
     * @param IStorage      $storage
     */
    public function __construct(ILocale $locale, IConfigurator $configurator, IStorage $storage)
    {
        parent::__construct($locale);

        $this->configurator = $configurator;

        $this->cache = new Cache($storage, 'Configurator-ConfiguratorTranslator');
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        $cacheKey = 'dictionary' . $this->locale->getId();
//        \Tracy\Debugger::fireLog('ConfiguratorDriver::loadTranslate; cacheKey ' . $cacheKey);
        $this->dictionary = $this->cache->load($cacheKey);
        if ($this->dictionary === null) {
//FIXME opravit nacitani list data!!!! musi brat v potaz to ze pokud je dev null translator musi brat values!!!
            $this->dictionary = [];//$this->configurator->getListData()->fetchPairs('ident', 'content');
            try {
                $this->cache->save($cacheKey, $this->dictionary, [
                    Cache::TAGS => ['saveCache'],
                ]);
            } catch (\Throwable $e) {
            }
        }
    }


    /**
     * Save translate.
     *
     * @param string $identification
     * @param        $message
     * @param null   $idLocale
     * @return string
     */
    protected function saveTranslate(string $identification, $message, $idLocale = null): string
    {
        return $this->configurator->setTranslation($identification, $message);
    }
}
