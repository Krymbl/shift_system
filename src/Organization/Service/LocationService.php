<?php

declare(strict_types=1);

namespace App\Organization\Service;

use App\Organization\Dto\LocationSaveDto;
use App\Organization\Entity\Location;
use App\Organization\Exception\LocationAlreadyClosedException;
use App\Organization\Exception\LocationHasRelatedDataException;
use App\Organization\Exception\LocationNameAlreadyTakenException;
use App\Organization\Exception\LocationNotClosedException;
use App\Organization\Exception\LocationNotFoundException;
use App\Organization\Repository\LocationRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class LocationService
{
    private const array UNIQUE_EXCEPTIONS = [
        'location_name_lower_uniq' => LocationNameAlreadyTakenException::class,
    ];

    public function __construct(
        private LocationRepository $locationRepository
    ) {
    }

    /**
     * @throws UniqueConstraintViolationException
     * @throws LocationNameAlreadyTakenException
     */
    public function create(LocationSaveDto $dto): Location
    {
        try {
            $location = new Location(
                name: $dto->name,
                address: $dto->address,
                phone: $dto->phone,
            );

            $this->locationRepository->save($location);

            return $location;
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }
    }

    /**
     * @throws UniqueConstraintViolationException
     * @throws LocationNameAlreadyTakenException
     */
    public function update(Location $location, LocationSaveDto $dto): void
    {
        try {
            $location->setName($dto->name);
            $location->setAddress($dto->address);
            $location->setPhone($dto->phone);

            $this->locationRepository->save($location);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }
    }

    /**
     * Вызывается только суперадмином в крайних случаях
     * @throws LocationHasRelatedDataException
     */
    public function delete(Location $location): void
    {
        try {
            $this->locationRepository->remove($location);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new LocationHasRelatedDataException($e);
        }
    }

    /**
     * @throws LocationAlreadyClosedException
     */
    public function close(Location $location): void
    {
        if ($location->isClosed()) throw new LocationAlreadyClosedException();

        $location->setClosedAt(new \DateTimeImmutable());
        $this->locationRepository->save($location);
    }

    /**
     * @throws LocationNotClosedException
     */
    public function open(Location $location): void
    {
        if (!$location->isClosed()) throw new LocationNotClosedException();

        $location->setClosedAt(null);
        $this->locationRepository->save($location);
    }

    /**
     * @throws LocationNotFoundException
     */
    public function getById(int $id): Location
    {
        $location = $this->locationRepository->find($id);

        if (null === $location) {
            throw new LocationNotFoundException;
        }

        return $location;
    }

    /**
     * @return list<Location>
     */
    public function getAll(): array
    {
        return $this->locationRepository->findAll();
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    public function mapUniqueViolation(UniqueConstraintViolationException $e): LocationNameAlreadyTakenException
    {
        /**
         * @var class-string<LocationNameAlreadyTakenException> $exceptionClass
         */
        foreach (self::UNIQUE_EXCEPTIONS as $indexName => $exceptionClass) {
            if (str_contains($e->getMessage(), $indexName)) {
                return new $exceptionClass($e);
            }
        }

        throw $e;
    }
}
