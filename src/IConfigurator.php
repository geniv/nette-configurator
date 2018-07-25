<?php declare(strict_types=1);

use Dibi\Fluent;


/**
 * Interface IConfigurator
 *
 * @author  geniv
 */
interface IConfigurator
{

    /**
     * Get list type.
     *
     * @return array
     */
    public function getListType(): array;


    /**
     * Delete type.
     *
     * @param string $type
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteType(string $type): int;


    /**
     * Get list data by type.
     *
     * @param string $type
     * @return Fluent
     */
    public function getListDataByType(string $type): Fluent;


    /**
     * Get data.
     *
     * @param int $id
     * @param int $idLocale
     * @return array
     */
    public function getData(int $id, int $idLocale = 0): array;


    /**
     * Delete data.
     *
     * @param int $id
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteData(int $id): int;
}
