<?php declare(strict_types=1);

use Dibi\Fluent;
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

    /** @var string */
    private $tableConfigurator, $tableConfiguratorIdent;
    /** @var Connection */
    private $connection;
    /** @var int */
    private $idLocale;
    /** @var Cache */
    private $cache;
    /** @var array */
    private $values;
    /** @var bool */
    private $autoCreate = true;


    /**
     * Configurator constructor.
     *
     * @param string     $prefix
     * @param Connection $connection
     * @param ILocale    $locale
     * @param IStorage   $storage
     */
    public function __construct(string $prefix, Connection $connection, ILocale $locale, IStorage $storage)
    {
        parent::__construct();

        // define table names
        $this->tableConfigurator = $prefix . self::TABLE_NAME;
        $this->tableConfiguratorIdent = $prefix . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'cache-Configurator');

        $this->idLocale = $locale->getId();

        $this->loadData();  // load data
    }


    /**
     * Set auto create.
     *
     * @param bool $status
     * @return Configurator
     */
    public function setAutoCreate(bool $status): self
    {
        $this->autoCreate = $status;
        return $this;
    }


    /**
     * Overloading is and get method.
     *
     * use:
     * echo: {control config:editor 'identEditor1'}
     * return: {control config:editor 'identEditor1', true}
     * return is enabled: $presenter['config']->isEnableEditor('identEditor1')
     * set data: $presenter['config']->setTranslator('ident', 'text')
     *
     * @param $name
     * @param $args
     * @return mixed
     * @throws Exception
     * @throws \Dibi\Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // exclude method
            // set method
            if (substr($name, 0, 3) == 'set' && isset($args[0]) && isset($args[1])) {
                $method = strtolower(substr($name, 3));
                $this->addData($method, $args[0], $args[1]); // insert data + return out of method
                return $args[1];
            }

            // load method name
            $method = strtolower(substr($name, 6));
            if (!isset($args[0])) {
                throw new Exception('Identification parameter is not used.');
            }
            $ident = $args[0];  // load identification
            $return = (isset($args[1]) ? $args[1] : false);

            // process enable
            if (substr($name, 0, 8) == 'isEnable') {
                $method = strtolower(substr($name, 8));
                if (isset($this->values[$method][$ident])) {
                    $block = $this->values[$method][$ident];
                    return $block['enable'];
                }
            }

            // create
            if ($this->autoCreate && (!isset($this->values[$method]) || !isset($this->values[$method][$ident]))) {
                $this->addData($method, $ident);    // insert
                $this->loadData();                  // reloading
            }

            // load
            if (isset($this->values[$method])) {
                $block = $this->values[$method];
                if (isset($block[$ident])) {
                    if ($return) {
                        return ($block[$ident]['enable'] ? $block[$ident]['content'] : null);
                    }
                    echo($block[$ident]['enable'] ? $block[$ident]['content'] : null);

                } else {
                    throw new Exception('Identification is not find: ' . $ident . '.');
                }
            } else {
                throw new Exception('Invalid block. Block ' . $method . ' don`t exists.');
            }
        }
    }


    /**
     * Get id identification.
     *
     * @param array $values
     * @return int
     */
    private function getIdIdentification(array $values): int
    {
        $key = 'getIdIdentification' . md5(implode($values));
        $result = $this->cache->load($key);
        if ($result === null) {
            $result = $this->connection->select('id')
                ->from($this->tableConfiguratorIdent)
                ->where($values)
                ->fetchSingle();

            $this->cache->save($key, $result, [
                Cache::EXPIRE => '30 minutes',
                Cache::TAGS   => ['loadData'],
            ]);
        }

        // insert new identification if not exist
        if (!$result) {
            $result = $this->connection->insert($this->tableConfiguratorIdent, $values)
                ->onDuplicateKeyUpdate('%a', $values)
                ->execute(Dibi::IDENTIFIER);
        }
        return $result;
    }


    /**
     * Add data.
     *
     * @param string $type
     * @param string $ident
     * @param string $content
     * @return Result|int|null
     * @throws \Dibi\Exception
     */
    private function addData(string $type, string $ident, string $content = '')
    {
        $result = null;
        $arr = ['ident' => $ident];
        // load identification
//        $idIdent = $this->connection->select('id')
//            ->from($this->tableConfiguratorIdent)
//            ->where($arr)
//            ->fetchSingle();
//
//        // insert new identification if not exist
//        if (!$idIdent) {
//            $idIdent = $this->connection->insert($this->tableConfiguratorIdent, $arr)
//                ->onDuplicateKeyUpdate('%a', $arr)
//                ->execute(Dibi::IDENTIFIER);
//        }
        $idIdent = $this->getIdIdentification($arr);

        // check exist configure id
        $conf = $this->connection->select('id')
            ->from($this->tableConfigurator)
            ->where(['id_locale' => null, 'id_ident' => $idIdent])
            ->fetchSingle();

        if (!$conf) {
            $values = [
                'id_locale' => null,    // save without idLocale! for default values
                'type'      => $type,
                'id_ident'  => $idIdent,
                'content'   => $content ?: '## ' . $type . ' - ' . $ident . ' ##',
                'enable'    => true,
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

            // load rows by type
            foreach ($types as $type) {
                $items = $this->loadDataByType($type);
                $values[$type] = $items->fetchAssoc('ident');
            }

            $this->cache->save('values' . $this->idLocale, $values, [
                Cache::EXPIRE => '30 minutes',
                Cache::TAGS   => ['loadData'],
            ]);
        }
        $this->values = $values;
    }


    /**
     * Load data by type.
     *
     * @param string $type
     * @return Fluent
     */
    public function loadDataByType(string $type): Fluent
    {
        $result = $this->connection->select('c.id, i.ident, ' .
            'IFNULL(lo_c.content, c.content) content, ' .
            'IFNULL(lo_c.enable, c.enable) enable, ' .
            'IFNULL(lo_c.id_locale, c.id_locale) id_locale')
            ->from($this->tableConfigurator)->as('c')
            ->join($this->tableConfiguratorIdent)->as('i')->on('i.id=c.id_ident')
            ->leftJoin($this->tableConfigurator)->as('lo_c')->on('lo_c.id_ident=i.id')->and('lo_c.id_locale=%i', $this->idLocale)
            ->where(['c.type' => $type, 'c.id_locale' => null])
            ->groupBy('i.id')
            ->orderBy('c.id_locale')->desc();
        return $result;
    }
}
