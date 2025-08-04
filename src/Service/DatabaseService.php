<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\City;
use App\Entity\UserCity;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Hydrator\NamingStrategy\MapNamingStrategy;
use Laminas\Hydrator\ReflectionHydrator;

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
            ->where(new Where()->equalTo("city", $city))
            ->where(new Where()->equalTo("phone_number", $phoneNumber));
        $result = $sql->prepareStatementForSqlObject($delete)->execute();

        return $result->getAffectedRows() === 1;
    }

    /**
     * getUsersTrackingCity returns a complete list of the users tracking weather, regardless of city.
     *
     * @return \Traversable<int, UserCity>
     */
    public function getUsersTrackingCity(string $city): HydratingResultSet|null
    {
        $sql = new Sql($this->adapter);
        $select = $sql
            ->select(self::TABLE)
            ->columns(['city', 'phone_number'])
            ->order('city ASC');
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $hydrator = new ReflectionHydrator;
            $hydrator->setNamingStrategy(MapNamingStrategy::createFromHydrationMap([
                'phone_number' => 'phoneNumber',
            ]));
            $resultSet = new HydratingResultSet($hydrator, new UserCity());
            return $resultSet->initialize($result);
        }

        return null;
    }

    /**
     * getTrackedCities retrieves a unique, sorted list of cities being tracked
     *
     * @return \Traversable<int, City>
     */
    public function getTrackedCities(): HydratingResultSet|null
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
            $resultSet = new HydratingResultSet(new ReflectionHydrator, new City());
            return $resultSet->initialize($result);
        }

        return null;
    }
}
