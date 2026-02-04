<?php

namespace App\DataFixtures;

use App\Story\AppStory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        AppStory::load();
    }
}
