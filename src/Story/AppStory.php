<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Story;
use App\Factory\CompanyFactory;
use App\Factory\CustomerFactory;
use Zenstruck\Foundry\Attribute\AsFixture;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        $user = UserFactory::createOne();
        CompanyFactory::createOne([
            'owner' => $user
        ]);
        CustomerFactory::createMany(50);
        UserFactory::createOne([
            'email' => 'admin@teste.com',
            'password' => 123,
            'roles' => ['ROLE_ADMIN'],
            'phone' => "19999999999",
            'name' => 'Ulisses',
        ]);
    }
}
