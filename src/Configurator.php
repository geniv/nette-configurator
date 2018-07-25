<?php declare(strict_types=1);

use Dibi\Fluent;
use Nette\Application\UI\Control;
use Dibi\Connection;
use Locale\ILocale;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class Configurator
 *
 * @author  geniv
 */
class Configurator extends Control implements IConfiguration
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
    private $idLocale, $idDefaultLocale;
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
     * @throws Exception
     * @throws Throwable
     */
    public function __construct(string $prefix, Connection $connection, ILocale $locale, IStorage $storage)
    {
        parent::__construct();

        // define table names
        $this->tableConfigurator = $prefix . self::TABLE_NAME;
        $this->tableConfiguratorIdent = $prefix . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'Configurator');

        $this->idLocale = $locale->getId();
        $this->idDefaultLocale = $locale->getIdDefault();

        $this->getData();  // load data
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
     * @param $name
     * @param $args
     * @return mixed|null
     * @throws Exception
     * @throws Throwable
     * @throws \Dibi\Exception
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // exclude method
            if (!isset($args[0])) {
                throw new Exception('Identification parameter is not used.');
            }
            $ident = $args[0];  // load identification
            $method = strtolower(substr($name, 6)); // load method name
            $return = (isset($args[1]) ? $args[1] : false); // load result

            // setter - set method (extended for translator)
            if (substr($name, 0, 3) == 'set' && isset($ident) && isset($args[1])) {
                $method = strtolower(substr($name, 3));
                $this->addData($method, $ident, $args[1]); // insert data
                return $args[1];    // return message only by create new translate
            }

            // enable method
            if (substr($name, 0, 8) == 'isEnable') {
                $method = strtolower(substr($name, 8));
                if (isset($this->values[$method][$ident])) {
                    $block = $this->values[$method][$ident];
                    return $block['enable'];    // return enable state
                }
            }

            // getter - get method
            if (substr($name, 0, 3) == 'get' && isset($ident)) {
                $method = strtolower(substr($name, 3));     // modify name
                $return = true;     // set only return
            }

            // create
            if ($this->autoCreate && (!isset($this->values[$method]) || !isset($this->values[$method][$ident]))) {
                $this->addData($method, $ident);    // insert
                $this->getData();                  // reloading
            }

            // load value
            if (isset($this->values[$method])) {
                $block = $this->values[$method];
                if (isset($block[$ident])) {
                    if ($return) {
                        return ($block[$ident]['enable'] ? $block[$ident]['content'] : null);
                    }
                    echo($block[$ident]['enable'] ? $block[$ident]['content'] : null);
                } else {
                    throw new Exception('Identification "' . $method . ':' . $ident . '" does not eixst.');
                }
            } else {
                throw new Exception('Invalid block. Block: "' . $method . '" does not exists.');
            }
        }
    }


    /**
     * Get id identification.
     *
     * @param array $values
     * @return int
     * @throws Exception
     * @throws Throwable
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

            // insert new identification if not exist
            if (!$result) {
                $result = $this->connection->insert($this->tableConfiguratorIdent, $values)->execute(Dibi::IDENTIFIER);
            }

            $this->cache->save($key, $result, [
                Cache::EXPIRE => '30 minutes',
                Cache::TAGS   => ['loadData'],
            ]);
        }
        return (int) $result;
    }


    /**
     * Add data.
     *
     * @param string $type
     * @param string $identification
     * @param string $content
     * @return int
     * @throws Throwable
     * @throws \Dibi\Exception
     */
    private function addData(string $type, string $identification, string $content = ''): int
    {
        $result = null;
        $arr = ['ident' => $identification];
        // load identification
        $idIdentification = $this->getIdIdentification($arr);

        // check exist configure id
        $conf = $this->connection->select('id')
            ->from($this->tableConfigurator)
            ->where(['id_locale' => $this->idDefaultLocale, 'id_ident' => $idIdentification])
            ->fetchSingle();

        if (!$conf) {
            // insert data
            $values = [
                'id_locale' => $this->idDefaultLocale,  // UQ 1/2 - always default create language
                'id_ident'  => $idIdentification,       // UQ 2/2
                'type'      => $type,
                'content'   => ($content ?: '## ' . $type . ' - ' . $identification . ' ##'),
                'enable'    => true,                    // always default enabled
            ];
            // only insert data
            $result = $this->connection->insert($this->tableConfigurator, $values)->execute();

            $this->cache->clean([
                Cache::TAGS => ['loadData'],
            ]);
        } else {
            // update data
            $result = $this->connection->update($this->tableConfigurator, ['content' => $content])->where(['id' => $conf])->execute();

            $this->cache->clean([
                Cache::TAGS => ['loadData'],
            ]);
        }
        return (int) $result;
    }


    /**
     * Get data.
     *
     * @throws Exception
     * @throws Throwable
     */
    private function getData()
    {
        $values = $this->cache->load('values' . $this->idLocale);
        if ($values === null) {
            $types = $this->getListType();

            // load rows by type
            foreach ($types as $type) {
                $items = $this->getListDataByType($type);
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
     * Get list data by type.
     *
     * @param string $type
     * @return Fluent
     */
    public function getListDataByType(string $type): Fluent
    {
        $result = $this->connection->select('ci.id, ci.ident, ' .
            'IFNULL(lo_c.id_locale, c.id_locale) id_locale, ' .
            'IFNULL(lo_c.content, c.content) content, ' .
            'IFNULL(lo_c.enable, c.enable) enable')
            ->from($this->tableConfiguratorIdent)->as('ci')
            ->join($this->tableConfigurator)->as('c')->on('c.id_ident=ci.id')->and(['c.id_locale' => $this->idDefaultLocale])
            ->leftJoin($this->tableConfigurator)->as('lo_c')->on('lo_c.id_ident=ci.id')->and(['lo_c.id_locale' => $this->idLocale])
            ->where('(%or)', ['lo_c.type' => $type, 'c.type' => $type]);
//        $result->test();
        return $result;
    }


    /**
     * Get list type.
     *
     * @return array
     */
    public function getListType(): array
    {
        return $this->connection->select('id, type')
            ->from($this->tableConfigurator)
            ->groupBy('type')
            ->orderBy(['type' => 'ASC'])
            ->fetchPairs('id', 'type');
    }


    /**
     * Delete type.
     *
     * @param string $type
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteType(string $type): int
    {
        $result = $this->connection->delete($this->tableConfigurator)
            ->where(['type' => $type]);
//        $result->test();
        return (int) $result->execute();
    }


    /**
     * Get data by type by id.
     *
     * @param string $type
     * @param int    $id
     * @return array
     */
    public function getDataByTypeById(string $type, int $id): array
    {
        $result = $this->getListDataByType($type)
            ->where(['c.id' => $id]);
//        $result->test();
        return (array) $result->fetch();
    }


    /**
     * Delete data.
     *
     * @param int $id
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteData(int $id): int
    {
        $result = $this->connection->delete($this->tableConfigurator)
            ->where(['id' => $id]);
//        $result->test();
        return (int) $result->execute();
    }
}
