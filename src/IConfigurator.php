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
     * Get data by type by id.
     *
     * @param string $type
     * @param int    $id
     * @return array
     */
    public function getDataByTypeById(string $type, int $id): array;


    /**
     * Delete data.
     *
     * @param int $id
     * @return int
     * @throws \Dibi\Exception
     */
    public function deleteData(int $id): int;
}
