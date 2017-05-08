<?php

use Dibi\Connection;
use Dibi\Result;
use Exception;
use LocaleServices\LocaleService;
use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class Configurator
 *
 * @author  geniv
 * @package Configurator
 */
class Configurator extends Control
{
    private $tableConfigurator, $tableConfiguratorIdent;
    private $database, $idLocale, $cache, $values;
    private $autoCreate = true;


    /**
     * Configurator constructor.
     *
     * @param               $tableConfigurator
     * @param Connection    $database
     * @param LocaleService $language
     * @param IStorage      $cacheStorage
     */
    public function __construct($tableConfigurator, Connection $database, LocaleService $language, IStorage $cacheStorage)
    {
        parent::__construct();

        $this->tableConfigurator = $tableConfigurator;
        $this->tableConfiguratorIdent = $tableConfigurator . '_ident';
        $this->database = $database;
        $this->cache = new Cache($cacheStorage, 'cache' . __CLASS__);

        $this->idLocale = $language->getId();

        $this->loadData();  // nacteni dat
    }


    /**
     * ovladani automatickeho vytvareni
     *
     * @param $status
     * @return $this
     */
    public function setAutoCreate($status)
    {
        $this->autoCreate = $status;
        return $this;
    }


    /**
     * volani uzivatelsky definovanych extra sloupcu
     *
     * @param $name
     * @param $args
     * @return mixed|void
     * @throws Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // nesmi zachytavat definovane metody
            $method = strtolower(substr($name, 6)); // nacteni jmena
            if (!isset($args[0])) {
                throw new Exception('Nebyl zadany parametr identu.');
            }
            $ident = $args[0];  // nacteni identu
            $return = (isset($args[1]) ? $args[1] : false);

            // nacteni enable
            if (substr($name, 0, 8) == 'isEnable') {
                $method = strtolower(substr($name, 8));
                if (isset($this->values[$method][$ident])) {
                    $block = $this->values[$method][$ident];
                    return $block->enable;
                }
            }

            // vytvareni
            if ($this->autoCreate && (!isset($this->values[$method]) || !isset($this->values[$method][$ident]))) {
                $this->addData($method, $ident);    // vlozeni
                $this->loadData();                  // znovunacteni
            }

            // nacitani
            if (isset($this->values[$method])) {
                $block = $this->values[$method];
                if (isset($block[$ident])) {
                    if ($return) {
                        return ($block[$ident]->enable ? $block[$ident]->content : null);
                    }
                    echo($block[$ident]->enable ? $block[$ident]->content : null);

                } else {
                    throw new Exception('Nebyl nalezeny ident ' . $ident . '.');
                }
            } else {
                throw new Exception('Nebyl nalezeny platny blok. Blok ' . $method . ' neexistuje.');
            }
        }
    }


    /**
     * vkladani polozky
     *
     * @param $type
     * @param $ident
     * @return Result|int|null
     */
    private function addData($type, $ident)
    {
        $arr = ['ident' => $ident];
        // nacte identifikator
        $id_ident = $this->database->select('id')
            ->from($this->tableConfiguratorIdent)
            ->where($arr)
            ->fetchSingle();

        // pokud se nenajde tak se vlozi novy
        if (!$id_ident) {
            $id_ident = $this->database->insert($this->tableConfiguratorIdent, $arr)
                ->onDuplicateKeyUpdate('%a', $arr)
                ->execute(dibi::IDENTIFIER);
        }

        // overeni existence
        $conf = $this->database->select('id')
            ->from($this->tableConfigurator)
            ->where(['id_locale' => null, 'id_ident' => $id_ident])
            ->fetchSingle();

        if (!$conf) {
            $values = [
                // 'id_locale' => $this->idLocale, // ukladani bez lokalizace a lokalizace se bude pridelovat dodatecne
                'type'     => $type,
                'id_ident' => $id_ident,
                'content'  => '## ' . $type . ' - ' . $ident . ' ##',
                'enable'   => 1,
            ];
            return $this->database->insert($this->tableConfigurator, $values)
                ->execute(Dibi::IDENTIFIER);
        }
        return null;
    }


    /**
     * nacitani a zpracovani dat
     *
     * @throws Exception
     * @throws Throwable
     */
    private function loadData()
    {
        $values = $this->cache->load('values' . $this->idLocale);
        if ($values === null) {
            $types = $this->database->select('id, type')
                ->from($this->tableConfigurator)
                ->groupBy('type')
                ->fetchPairs('id', 'type');

            foreach ($types as $type) {
                $items = $this->database->select('c.id, i.ident, c.content, c.enable')
                    ->from($this->tableConfigurator)->as('c')
                    ->join($this->tableConfiguratorIdent)->as('i')->on('i.id=c.id_ident')
                    ->where('c.type=%s', $type)
                    ->where('(c.id_locale=%i OR c.id_locale IS NULL)', $this->idLocale)
                    ->orderBy('c.id_locale')->desc();

                $values[$type] = $items->fetchAssoc('ident');
            }

            $this->cache->save('values' . $this->idLocale, $values, [
                Cache::EXPIRE => '30 minutes',
                Cache::TAGS   => ['loadData'],
            ]);
        }
        $this->values = $values;
    }
}
