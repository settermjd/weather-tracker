<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;

final readonly class DatabaseService
{
    const string TABLE = "tracking";
    private Adapter $adapter;

    public function __construct()
    {
        $this->adapter = new Adapter([
            'driver'   => 'Pdo_Sqlite',
            'database' => __DIR__ . '/../../sqlite.db',
        ]);
    }

    /**
     * startTracking starts tracking the weather in a city for a user
     */
    public function startTracking(string $phoneNumber, string $city): bool
    {
        $sql = new Sql($this->adapter);
        $insert = $sql
            ->insert(self::TABLE)
            ->columns(["city", "phone_number"])
            ->values([
                "city" => $city,
                "phone_number" => $phoneNumber,
            ]);
        $result = $sql->prepareStatementForSqlObject($insert)->execute();

        return $result->getAffectedRows() === 1;
    }

    /**
     * stopTracking stops tracking the weather in a city for a given user
     */
    public function stopTracking(string $phoneNumber, string $city): bool
    {
        $sql = new Sql($this->adapter);
        $delete = $sql
            ->delete(self::TABLE)
            ->where(new Where([
                "city" => $city,
                "phone_number" => $phoneNumber,
            ]));
        $result = $sql->prepareStatementForSqlObject($delete)->execute();

        return $result->getAffectedRows() === 1;
    }

    /**
     * getTrackingData returns a complete list of the users tracking weather, regardless of city.
     */
    public function getTrackingData(string $city): ResultSetInterface|null
    {
        $sql = new Sql($this->adapter);
        $select = $sql
            ->select(self::TABLE)
            ->columns(['city', 'phone_number'])
            ->order('city ASC');
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            return $resultSet->initialize($result);
        }

        return null;
    }

    /**
     * getTrackedCities retrieves a unique, sorted list of cities being tracked
     */
    public function getTrackedCities(): ResultSetInterface|null
    {
        $sql = new Sql($this->adapter);
        $select = $sql
            ->select(self::TABLE)
            ->columns(
                [
                    'city' => new Expression("DISTINCT `city`")
                ]
            )
            ->order('city ASC');
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            return $resultSet->initialize($result);
        }

        return null;
    }
}
