<?php declare(strict_types=1);

use Dibi\Fluent;


/**
 * Interface IConfigurator
 *
 * @author  geniv
 */
interface IConfigurator
{

//    /**
//     * Get list ident.
//     *
//     * @return array
//     */
//    public function getListIdent(): array;


//    /**
//     * Delete type.
//     *
//     * @param string $type
//     * @return int
//     * @throws \Dibi\Exception
//     */
//    public function deleteType(string $type): int;


    /**
     * Get list data.
     *
     * @return Fluent
     */
    public function getListData(): Fluent;


    /**
     * Get list data by type.
     *
     * @param string $type
     * @return Fluent
     */
    public function getListDataByType(string $type): Fluent;


    /**
     * Get list data type.
     *
     * @return array
     */
    public function getListDataType(): array;


    /**
     * Get data by id.
     *
     * @param int $id
     * @param int $idLocale
     * @return array
     */
    public function getDataById(int $id, int $idLocale = 0): array;


//    /**
//     * Add data.
//     *
//     * @param array $values
//     * @return int
//     * @throws \Dibi\Exception
//     */
//    public function addData(array $values): int;


//    /**
//     * Edit data.
//     *
//     * @param int   $id
//     * @param array $values
//     * @return int
//     * @throws \Dibi\Exception
//     */
//    public function editData(int $id, array $values): int;


//    /**
//     * Delete data.
//     *
//     * @param int $id
//     * @return int
//     * @throws \Dibi\Exception
//     */
//    public function deleteData(int $id): int;
}
