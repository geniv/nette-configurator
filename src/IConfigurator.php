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
     * Set auto create.
     *
     * @param bool $status
     */
    public function setAutoCreate(bool $status);


    /**
     * Is enable.
     *
     * @param string $identification
     * @return bool
     */
    public function isEnable(string $identification): bool;


    /**
     * Get values.
     *
     * @return array
     */
    public function getValues(): array;


    /**
     * Get values by type.
     *
     * @param string $type
     * @return array
     */
    public function getValuesByType(string $type): array;


    /**
     * Get value.
     *
     * @param string $identification
     * @return mixed
     */
    public function getValue(string $identification);


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
