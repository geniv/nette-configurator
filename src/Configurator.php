<?php

use Dibi\Connection;
use Dibi\Result;
use LocaleServices\LocaleService;
use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class Configurator
 *
 * @author  geniv
 */
class Configurator extends Control
{
    /** @var string tables name */
    private $tableConfigurator, $tableConfiguratorIdent;
    /** @var Connection connection */
    private $connection;
    /** @var int id locale */
    private $idLocale;
    /** @var Cache caching */
    private $cache;
    /** @var array internal data */
    private $values;
    /** @var bool implicit value */
    private $autoCreate = true;


    /**
     * Configurator constructor.
     *
     * @param               $tableConfigurator
     * @param Connection    $connection
     * @param LocaleService $localeService
     * @param IStorage      $storage
     */
    public function __construct($tableConfigurator, Connection $connection, LocaleService $localeService, IStorage $storage)
    {
        parent::__construct();

        $this->tableConfigurator = $tableConfigurator;
        $this->tableConfiguratorIdent = $tableConfigurator . '_ident';
        $this->connection = $connection;
        $this->cache = new Cache($storage, 'cache' . __CLASS__);

        $this->idLocale = $localeService->getId();

        $this->loadData();  // nacteni dat
    }


    /**
     * Control automatic create ident.
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
     * Overloading is and get method.
     *
     * LATTE:
     * echo: {control config:editor 'identEditor1'}
     * return: {control config:editor 'identEditor1', true}
     * return is enabled: $presenter['config']->isEnableEditor('identEditor1')
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
     * Insert item.
     *
     * @param $type
     * @param $ident
     * @return Result|int|null
     */
    private function addData($type, $ident)
    {
        $arr = ['ident' => $ident];
        // nacte identifikator
        $id_ident = $this->connection->select('id')
            ->from($this->tableConfiguratorIdent)
            ->where($arr)
            ->fetchSingle();

        // pokud se nenajde tak se vlozi novy
        if (!$id_ident) {
            $id_ident = $this->connection->insert($this->tableConfiguratorIdent, $arr)
                ->onDuplicateKeyUpdate('%a', $arr)
                ->execute(Dibi::IDENTIFIER);
        }

        // overeni existence
        $conf = $this->connection->select('id')
            ->from($this->tableConfigurator)
            ->where(['id_locale' => null, 'id_ident' => $id_ident])
            ->fetchSingle();

        if (!$conf) {
            $values = [
                'id_locale' => null,    // ukladani bez lokalizace, lokalizace se bude pridelovat dodatecne
                'type'      => $type,
                'id_ident'  => $id_ident,
                'content'   => '## ' . $type . ' - ' . $ident . ' ##',
                'enable'    => 1,
            ];
            return $this->connection->insert($this->tableConfigurator, $values)
                ->execute(Dibi::IDENTIFIER);
        }
        return null;
    }


    /**
     * Load data.
     */
    private function loadData()
    {
        $values = $this->cache->load('values' . $this->idLocale);
        if ($values === null) {
            $types = $this->connection->select('id, type')
                ->from($this->tableConfigurator)
                ->groupBy('type')
                ->fetchPairs('id', 'type');

            foreach ($types as $type) {
                $items = $this->connection->select('c.id, i.ident, c.content, c.enable')
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
