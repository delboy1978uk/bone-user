<?php

declare(strict_types=1);

namespace Bone\User\Fixtures;

use Del\Entity\User;
use Del\Person\Entity\Person;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadUsers implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $person = new Person();
        $person->setFirstName('Super');
        $person->setLastName('McUser');
        $user = new  User();
        $user->getPerson($person);
        $user->setEmail('man@work.com');
        $manager->persist($user);
        $manager->flush();

        $person = new Person();
        $person->setFirstName('Super');
        $person->setLastName('McAdmin');
        $user = new  User();
        $user->getPerson($person);
        $user->setEmail('staff@work.com');
        $manager->persist($user);
        $manager->flush();

        $person = new Person();
        $person->setFirstName('Norma');
        $person->setLastName('McUser');
        $user = new  User();
        $user->getPerson($person);
        $user->setEmail('woman@home.com');
        $manager->persist($user);
        $manager->flush();
    }
}
