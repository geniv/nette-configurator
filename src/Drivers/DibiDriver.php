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
     * Get internal id identification.
     *
     * @deprecated
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
//TODO zjednodusit!!
            // insert new identification if not exist
            if (!$result) {
                $result = $this->connection->insert($this->tableConfiguratorIdent, $values)->execute(Dibi::IDENTIFIER);
            }

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
     * Save internal data.
     *
     * @internal
     * @param string $type
     * @param string $identification
     * @param string $content
     * @return int
     * @throws \Dibi\Exception
     */
    protected function saveInternalData(string $type, string $identification, string $content = ''): int
    {
        $result = null;
        $arr = ['ident' => $identification, 'type' => $type];
        // load identification
        $idIdentification = $this->getInternalIdIdentification($arr);
//FIXME prepsat!!
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
                'content'   => ($content ?: $this->getDefaultContent($type, $identification)),
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
     * Load internal data.
     *
     * @internal
     */
    protected function loadInternalData()
    {
        $cacheKey = 'loadInternalData' . $this->locale->getId();
        $this->values = $this->cache->load($cacheKey);
        if ($this->values === null) {
            $this->values = $this->getListData()->fetchAssoc('ident');
            try {
                $this->cache->save($cacheKey, $this->values, [
                    Cache::TAGS => ['loadData'],
                ]);
            } catch (\Throwable $e) {
            }
        }
    }
}
