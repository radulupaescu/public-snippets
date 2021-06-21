<?php

namespace Examples\ActiveRecord;

abstract class ActiveRecord
{
    /**
     * Not the most beautiful solution, but because we depend on the database and have no container
     * to solve our dependency injection we must hack our way through the inconveniences.
     *
     * Static property for storing the database connection.
     *
     * @var \PDO $database
     */
    static \PDO $database;

    /**
     * Handles insert or update for any entity extending this class.
     *
     * @return bool
     */
    abstract public function save(): bool;

    /**
     * Handles delete for any entity extending this class.
     *
     * @return bool
     */
    abstract public function delete(): bool;

    /**
     * Method for returning all entities of this type from the database.
     *
     * @return self[]
     */
    abstract static public function findAll(): array;

    /**
     * Method for retrieving a specific entity from the database by using the unique id.
     *
     * @param int $id
     * @return self|null
     */
    abstract static public function findById(int $id): ?self;
}
