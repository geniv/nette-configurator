<?php declare(strict_types=1);

namespace Configurator\Drivers;

use Configurator\IConfigurator;
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
class DibiDriver implements IConfigurator
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
    /** @var ILocale */
    private $locale;
    /** @var int */
    private $idDefaultLocale;


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
        // define table names
        $this->tableConfigurator = $prefix . self::TABLE_NAME;
        $this->tableConfiguratorIdent = $prefix . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'Configurator');

        $this->locale = $locale;
        $this->idDefaultLocale = $locale->getIdDefault();
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
        $this->cache->clean([Cache::TAGS => 'loadData']);
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
}
