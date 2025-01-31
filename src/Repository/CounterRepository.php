<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Counter;

/**
 * CounterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CounterRepository extends EntityRepository
{
    public function addCount($userInfo, $type, $date = null)
    {
        if (!$date) {
            $date = date('Y-m');
        }
        $counter = $this->_em->getRepository('App:Counter')
                ->findOneBy([
                    'date'      => $date,
                    'user_info' => $userInfo,
                    'type'      => $type,
                ]);

        if (!$counter) {
            $counter = new Counter();
            $counter->setUserInfo($userInfo);
            $counter->setCount(1);
            $counter->setDate($date);
            $counter->setType($type);
            $this->_em->persist($counter);
            $this->_em->flush();

            return 1;
        }

        $counter->setCount($counter->getCount() + 1);
        $this->_em->persist($counter);
        $this->_em->flush();

        return $counter->getCount();
    }

    public function getCount($userInfo, $type, $date = null)
    {
        if (!$date) {
            $date = date('Y-m');
        }
        $counter = $this->_em->getRepository('App:Counter')
                ->findOneBy([
                    'date'      => $date,
                    'user_info' => $userInfo,
                    'type'      => $type,
                ]);

        if (!$counter) {
            return 0;
        }

        return $counter->getCount();
    }
}
