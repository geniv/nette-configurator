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
        $cacheKey = 'loadTranslate' . $this->locale->getId();
//        \Tracy\Debugger::fireLog('ConfiguratorDriver::loadTranslate; cacheKey ' . $cacheKey);
        $this->dictionary = $this->cache->load($cacheKey);
        if ($this->dictionary === null) {
            $translation = $this->configurator->getValuesByType('translation');

            $this->dictionary = array_map(function ($item) {
                return $item['content'];
            }, $translation);

//            $this->dictionary = $this->configurator->getListData()->fetchPairs('ident', 'content');
            try {
                $this->cache->save($cacheKey, $this->dictionary, [
                    Cache::TAGS => ['saveCache'],
                ]);
            } catch (\Throwable $e) {
            }
        }

        // process default translate
        $this->searchDefaultTranslate();
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


    /**
     * Search default translate.
     *
     * @internal
     */
    protected function searchDefaultTranslate()
    {
        $searchContent = $this->configurator->getSearchContent();
        $this->setSearchPath($searchContent->getSearchMask(), $searchContent->getSearchPath(), $searchContent->getExcludePath());
        // call parent
        parent::searchDefaultTranslate();
    }
}
