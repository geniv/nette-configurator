<?php

use Nette\Application\UI\Control;
use Dibi\Connection;
use Dibi\Result;
use Locale\ILocale;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class Configurator
 *
 * @author  geniv
 */
class Configurator extends Control
{
    // define constant table names
    const
        TABLE_NAME = 'configurator',
        TABLE_NAME_IDENT = 'configurator_ident';

    /** @var string tables name */
    private $tableConfigurator, $tableConfiguratorIdent;
    /** @var Connection database connection from DI */
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
     * @param            $prefix
     * @param Connection $connection
     * @param ILocale    $locale
     * @param IStorage   $storage
     */
    public function __construct($prefix, Connection $connection, ILocale $locale, IStorage $storage)
    {
        parent::__construct();

        // define table names
        $this->tableConfigurator = $prefix . self::TABLE_NAME;
        $this->tableConfiguratorIdent = $prefix . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'cache-Configurator');

        $this->idLocale = $locale->getId();

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
     * set data: $presenter['config']->setTranslator('ident', 'text')
     *
     * @param $name
     * @param $args
     * @return mixed|void
     * @throws Exception
     * @throws \Dibi\Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // nesmi zachytavat definovane metody
            $method = strtolower(substr($name, 6)); // nacteni jmena
            if (!isset($args[0])) {
                throw new Exception('Don`t use Nebyl zadany parametr identu.');
            }
            $ident = $args[0];  // nacteni identu
            $return = (isset($args[1]) ? $args[1] : false);

            if (substr($name, 0, 3) == 'set') {
                $method = strtolower(substr($name, 3));

                $this->addData($method, $ident, $ident);
            }

            // nacteni enable
            if (substr($name, 0, 8) == 'isEnable') {
                $method = strtolower(substr($name, 8));
                if (isset($this->values[$method][$ident])) {
                    $block = $this->values[$method][$ident];
                    return $block['enable'];
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
                        return ($block[$ident]['enable'] ? $block[$ident]['content'] : null);
                    }
                    echo($block[$ident]['enable'] ? $block[$ident]['content'] : null);

                } else {
                    throw new Exception('Don`t find identification: ' . $ident . '.');
                }
            } else {
                throw new Exception('Don`t find valid block. Block ' . $method . ' don`t exists.');
            }
        }
    }


    /**
     * Insert item.
     *
     * @param      $type
     * @param      $ident
     * @param null $content
     * @return Result|int|null
     * @throws \Dibi\Exception
     */
    private function addData($type, $ident, $content = null)
    {
        $result = null;
        $arr = ['ident' => $ident];
        // nacte identifikator
        $idIdent = $this->connection->select('id')
            ->from($this->tableConfiguratorIdent)
            ->where($arr)
            ->fetchSingle();

        // pokud se nenajde tak se vlozi novy
        if (!$idIdent) {
            $idIdent = $this->connection->insert($this->tableConfiguratorIdent, $arr)
                ->onDuplicateKeyUpdate('%a', $arr)
                ->execute(Dibi::IDENTIFIER);
        }

        // overeni existence
        $conf = $this->connection->select('id')
            ->from($this->tableConfigurator)
            ->where(['id_locale' => null, 'id_ident' => $idIdent])
            ->fetchSingle();

        if (!$conf) {
            $values = [
                'id_locale' => null,    // ukladani bez lokalizace, lokalizace se bude pridelovat dodatecne
                'type'      => $type,
                'id_ident'  => $idIdent,
                'content'   => $content ?: '## ' . $type . ' - ' . $ident . ' ##',
                'enable'    => 1,
            ];
            $result = $this->connection->insert($this->tableConfigurator, $values)
                ->execute();

            $this->cache->clean([
                Cache::TAGS => ['loadData'],
            ]);
        }
        return $result;
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
                $items = $this->loadDataByType($type);
//                $items = $this->connection->select('c.id, i.ident, IFNULL(lo_c.content, c.content) content, IFNULL(lo_c.enable, c.enable) enable')
//                    ->from($this->tableConfigurator)->as('c')
//                    ->join($this->tableConfiguratorIdent)->as('i')->on('i.id=c.id_ident')
//                    ->leftJoin($this->tableConfigurator)->as('lo_c')->on('lo_c.id_ident=i.id')->and('lo_c.id_locale=%i', $this->idLocale)
//                    ->where(['c.type' => $type, 'c.id_locale' => null])
//                    ->groupBy('i.id')
//                    ->orderBy('c.id_locale')->desc();

                $values[$type] = $items->fetchAssoc('ident');
            }

            $this->cache->save('values' . $this->idLocale, $values, [
                Cache::EXPIRE => '30 minutes',
                Cache::TAGS   => ['loadData'],
            ]);
        }
        $this->values = $values;
    }


    public function loadDataByType($type)
    {
        $result = $this->connection->select('c.id, i.ident, IFNULL(lo_c.content, c.content) content, IFNULL(lo_c.enable, c.enable) enable')
            ->from($this->tableConfigurator)->as('c')
            ->join($this->tableConfiguratorIdent)->as('i')->on('i.id=c.id_ident')
            ->leftJoin($this->tableConfigurator)->as('lo_c')->on('lo_c.id_ident=i.id')->and('lo_c.id_locale=%i', $this->idLocale)
            ->where(['c.type' => $type, 'c.id_locale' => null])
            ->groupBy('i.id')
            ->orderBy('c.id_locale')->desc();
        return $result;
    }
}
