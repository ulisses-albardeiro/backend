<?php

namespace App\Factory;

use App\Entity\Company;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Company>
 */
final class CompanyFactory extends PersistentObjectFactory
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
        return Company::class;
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

            'name' => self::faker()->company(),
            'tradingName' => self::faker()->company() . ' ME',
            'registrationNumber' => self::faker()->numerify('##############'),
            'stateRegistration' => self::faker()->numerify('#########'),

            'email' => self::faker()->companyEmail(),
            'phone' => self::faker()->numerify('###########'),
            'website' => 'mysite.com',
            'logo' => 'default_logo.png',

            'zipCode' => self::faker()->postcode(),
            'street' => self::faker()->streetName(),
            'number' => self::faker()->buildingNumber(),
            'complement' => self::faker()->secondaryAddress(),
            'neighborhood' => self::faker()->word(),
            'city' => self::faker()->city(),
            'state' => self::faker()->stateAbbr(),

            'owner' => UserFactory::randomOrCreate(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year', 'now')),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-6 months', 'now')),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
           // ->afterInstantiate(function(Company $company): void {})
        ;
    }
}
