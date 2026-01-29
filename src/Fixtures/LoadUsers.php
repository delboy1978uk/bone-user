<?php

declare(strict_types=1);

namespace Bone\User\Fixtures;

use DateTime;
use DateTimeZone;
use Del\Entity\User;
use Del\Person\Entity\Person;
use Del\Value\User\State;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadUsers implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $password = password_hash('123456', PASSWORD_BCRYPT, ['cost' => 14]);
        $registrationDate = new DateTime('now', new DateTimeZone('UTC'));
        $person = new Person();
        $person->setFirstName('Super');
        $person->setLastName('McUser');
        $user = new  User();
        $user->setPerson($person);
        $user->setEmail('man@work.com');
        $user->setPassword($password);
        $user->setState(new State(State::STATE_ACTIVATED));
        $user->setRegistrationDate($registrationDate);
        $manager->persist($user);
        $manager->flush();

        $person = new Person();
        $person->setFirstName('Super');
        $person->setLastName('McAdmin');
        $user = new  User();
        $user->setPerson($person);
        $user->setEmail('staff@work.com');
        $user->setPassword($password);
        $user->setState(new State(State::STATE_ACTIVATED));
        $user->setRegistrationDate($registrationDate);
        $manager->persist($user);
        $manager->flush();

        $person = new Person();
        $person->setFirstName('Norma');
        $person->setLastName('McUser');
        $user = new  User();
        $user->setPerson($person);
        $user->setEmail('woman@home.com');
        $user->setPassword($password);
        $user->setState(new State(State::STATE_ACTIVATED));
        $user->setRegistrationDate($registrationDate);
        $manager->persist($user);
        $manager->flush();
    }
}
