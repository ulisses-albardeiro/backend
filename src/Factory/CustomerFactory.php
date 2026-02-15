<?php

namespace App\Factory;

use App\Entity\Customer;
use App\Enum\CustomerType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Customer>
 */
final class CustomerFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    #[\Override]
    public static function class(): string
    {
        return Customer::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'company' => CompanyFactory::randomOrCreate(),

            'type' => self::faker()->randomElement(CustomerType::cases()),
            'name' => self::faker()->name(),
            'tradingName' => self::faker()->company(),

            'document' => self::faker()->randomElement([
                self::faker()->numerify('###########'),
                self::faker()->numerify('##############'),
            ]),

            'stateRegistration' => self::faker()->numerify('#########'),

            'email' => self::faker()->safeEmail(),
            'phone' => self::faker()->numerify('###########'),

            'zipCode' => self::faker()->numerify('########'),
            'street' => self::faker()->streetName(),
            'number' => self::faker()->buildingNumber(),
            'complement' => self::faker()->secondaryAddress(),
            'neighborhood' => self::faker()->word(),
            'city' => self::faker()->city(),
            'state' => self::faker()->stateAbbr(),

            'status' => self::faker()->boolean(80),
            'notes' => self::faker()->paragraph(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year')),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Customer $customer): void {})
        ;
    }
}
