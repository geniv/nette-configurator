<?php declare(strict_types=1);

namespace Configurator;

use Dibi\IDataSource;


/**
 * Interface IConfigurator
 *
 * @package Configurator
 * @author  geniv
 */
interface IConfigurator
{

    /**
     * Get list data.
     *
     * @param int|null $idLocale
     * @return IDataSource
     */
    public function getListData(int $idLocale = null): IDataSource;


    /**
     * Edit data.
     *
     * @param int   $id
     * @param array $values
     * @return int
     */
    public function editData(int $id, array $values): int;


    /**
     * Delete data.
     *
     * @param int $id
     * @return int
     */
    public function deleteData(int $id): int;


    /**
     * Clean cache.
     */
    public function cleanCache();
}
