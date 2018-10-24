<?php declare(strict_types=1);

namespace Configurator\Drivers;

use Configurator\Configurator;
use dibi;
use Dibi\Connection;
use Dibi\Fluent;
use Locale\ILocale;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class DibiDriver
 *
 * @author  geniv
 * @package Configurator\Drivers
 */
class DibiDriver extends Configurator
{
    // define constant table names
    const
        TABLE_NAME = 'configurator',
        TABLE_NAME_IDENT = 'configurator_ident';

    /** @var Connection */
    private $connection;
    /** @var string */
    private $tableConfigurator, $tableConfiguratorIdent;
    /** @var Cache */
    private $cache;


    /**
     * DibiDriver constructor.
     *
     * @param string     $prefix
     * @param Connection $connection
     * @param ILocale    $locale
     * @param IStorage   $storage
     */
    public function __construct(string $prefix, Connection $connection, ILocale $locale, IStorage $storage)
    {
        parent::__construct($locale);

        // define table names
        $this->tableConfigurator = $prefix . self::TABLE_NAME;
        $this->tableConfiguratorIdent = $prefix . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'Configurator');
    }


    /**
     * Get list data.
     *
     * @param int|null $idLocale
     * @return Fluent
     */
    public function getListData(int $idLocale = null): Fluent
    {
        $result = $this->connection->select('c.id, c.id_ident, ci.ident, ci.type, ' .
            'IFNULL(lo_c.id_locale, c.id_locale) id_locale, ' .
            'IFNULL(lo_c.content, c.content) content, ' .
            'IFNULL(lo_c.enable, c.enable) enable')
            ->from($this->tableConfiguratorIdent)->as('ci')
            ->join($this->tableConfigurator)->as('c')->on('c.id_ident=ci.id')->and(['c.id_locale' => $this->idDefaultLocale])
            ->leftJoin($this->tableConfigurator)->as('lo_c')->on('lo_c.id_ident=ci.id')->and(['lo_c.id_locale' => $idLocale ?: $this->locale->getId()]);
        return $result;
    }


    /**
     * Get list data by type.
     *
     * @param string   $type
     * @param int|null $idLocale
     * @return Fluent
     */
    public function getListDataByType(string $type, int $idLocale = null): Fluent
    {
        $result = $this->getListData($idLocale)
            ->where(['ci.type' => $type]);
        return $result;
    }


    /**
     * Get list data type.
     *
     * @return array
     */
    public function getListDataType(): array
    {
        return $this->connection->select('id, type')
            ->from($this->tableConfiguratorIdent)
            ->groupBy('type')
            ->orderBy(['type' => 'ASC'])
            ->fetchPairs('id', 'type');
    }


    /**
     * Get data by id.
     *
     * @param int      $idIdent
     * @param int|null $idLocale
     * @return array
     */
    public function getDataById(int $idIdent, int $idLocale = null): array
    {
        $result = $this->getListData($idLocale)
            ->where(['c.id_ident' => $idIdent]);
        return (array) ($result->fetch() ?: []);
    }


    /**
     * Get list ident.
     *
     * @return array
     */
    public function getListIdent(): array
    {
        return $this->connection->select('id, ident')
            ->from($this->tableConfiguratorIdent)
            ->fetchPairs('id', 'ident');
    }


    /**
     * Edit data.
     *
     * @param int   $id
     * @param array $values
     * @return int
     * @throws \Dibi\Exception
     */
    public function editData(int $id, array $values): int
    {
        $result = $this->connection->update($this->tableConfigurator, $values)
            ->where(['id' => $id]);
        return (int) $result->execute();
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
        return (int) $result->execute();
    }


    /**
     * Clean cache.
     */
    public function cleanCache()
    {
        // internal clean cache
        $this->cache->clean([
            Cache::TAGS => ['loadData'],
        ]);
    }


    /**
     * Get data by ident.
     *
     * @param string   $ident
     * @param int|null $idLocale
     * @return array
     */
    public function getDataByIdent(string $ident, int $idLocale = null): array
    {
        $result = $this->getListData($idLocale)
            ->where(['ci.ident' => $ident]);
        return (array) ($result->fetch() ?: []);
    }


    /**
     * Get internal id identification.
     *
     * @param array $values
     * @return int
     * @throws \Dibi\Exception
     */
    protected function getInternalIdIdentification(array $values): int
    {
        $cacheKey = 'getIdIdentification' . md5(implode($values));
        $result = $this->cache->load($cacheKey);
        if ($result === null) {
            $result = $this->connection->select('id')
                ->from($this->tableConfiguratorIdent)
                ->where($values)
                ->fetchSingle();

            // insert new identification if not exist
            if (!$result) {
                $result = $this->connection->insert($this->tableConfiguratorIdent, $values)->execute(Dibi::IDENTIFIER);
            }

            //Cache::EXPIRE => '30 minutes',
            try {
                $this->cache->save($cacheKey, $result, [
                    Cache::TAGS => ['loadData'],
                ]);
            } catch (\Throwable $e) {
            }
        }
        return (int) $result;
    }


    /**
     * Add internal data.
     *
     * @internal
     * @param string $type
     * @param string $identification
     * @param string $content
     * @return int
     * @throws \Dibi\Exception
     */
    protected function addInternalData(string $type, string $identification, string $content = ''): int
    {
        $result = null;
        $arr = ['ident' => $identification, 'type' => $type];
        // load identification
        $idIdentification = $this->getInternalIdIdentification($arr);

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
                'content'   => ($content ?: '## ' . $type . ' - ' . $identification . ' ##'),
                'enable'    => true,                    // always default enabled
            ];
            // only insert data
            $result = $this->connection->insert($this->tableConfigurator, $values)->execute();
        } else {
            // update data
            $result = $this->connection->update($this->tableConfigurator, ['content' => $content])->where(['id' => $conf])->execute();
        }
        $this->cleanCache();
        return (int) $result;
    }


    /**
     * Get internal data.
     *
     * @internal
     * @throws \Throwable
     */
    protected function getInternalData()
    {
        $cacheKey = 'values' . $this->locale->getId();
        $values = $this->cache->load($cacheKey);
        if ($values === null) {
            $types = $this->getListDataType();

            // load rows by type
            foreach ($types as $type) {
                $items = $this->getListDataByType($type);
                $values[$type] = $items->fetchAssoc('ident');
            }

            //Cache::EXPIRE => '30 minutes',
            $this->cache->save($cacheKey, $values, [
                Cache::TAGS => ['loadData'],
            ]);
        }
        $this->values = $values;
    }
}
