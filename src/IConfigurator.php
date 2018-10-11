<?php declare(strict_types=1);

use Dibi\Fluent;


/**
 * Interface IConfigurator
 *
 * @author  geniv
 */
interface IConfigurator
{
    // define constant table names
    const
        TABLE_NAME = 'configurator',
        TABLE_NAME_IDENT = 'configurator_ident';


    /**
     * Get list ident.
     *
     * @return array
     */
    public function getListIdent(): array;


    /**
     * Get list data.
     *
     * @param int|null $idLocale
     * @return Fluent
     */
    public function getListData(int $idLocale = null): Fluent;


    /**
     * Get list data by type.
     *
     * @param string   $type
     * @param int|null $idLocale
     * @return Fluent
     */
    public function getListDataByType(string $type, int $idLocale = null): Fluent;


    /**
     * Get list data type.
     *
     * @return array
     */
    public function getListDataType(): array;


    /**
     * Get data by id.
     *
     * @param int      $idIdent
     * @param int|null $idLocale
     * @return array
     */
    public function getDataById(int $idIdent, int $idLocale = null): array;


    /**
     * Edit data.
     *
     * @param int   $id
     * @param array $values
     * @return int
     * @throws \Dibi\Exception
     */
    public function editData(int $id, array $values): int;


    /**
     * Delete data.
     *
     * @param int $id
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteData(int $id): int;


    /**
     * Clean cache.
     */
    public function cleanCache();


    /**
     * Get data by ident.
     *
     * @param string   $ident
     * @param int|null $idLocale
     * @return array
     */
    public function getDataByIdent(string $ident, int $idLocale = null): array;
}
