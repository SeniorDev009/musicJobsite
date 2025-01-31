<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProjectInviteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectInviteRepository extends EntityRepository
{
    /**
     * Get project invites for user
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function getByUser($userInfoId)
    {
        $q = $this->createQueryBuilder('pi')
                ->select('pi, p, ui')
                ->innerJoin('pi.project', 'p')
                ->innerJoin('p.user_info', 'ui')
                ->where('pi.user_info = :userInfoId')
                ->orderBy('pi.created_at', 'DESC');

        $params = [
            ':userInfoId' => $userInfoId,
        ];
        $q->setParameters($params);

        $query = $q->getQuery();

        return $query->execute();
    }

    /**
     * Get project invites for user count
     *
     * @param int $userInfoId
     *
     * @return int
     */
    public function getByUserCount($userInfoId)
    {
        $q = $this->createQueryBuilder('pi')
                ->select('count(pi)')
                ->where('pi.user_info = :userInfoId');

        $params = [
            ':userInfoId' => $userInfoId,
        ];
        $q->setParameters($params);

        $query = $q->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Get project invites for user that haven't expired
     * past bids due date
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function getByUserProjectNotExpired($userInfoId)
    {
        $q = $this->createQueryBuilder('pi')
                ->select('pi, p, ui')
                ->innerJoin('pi.project', 'p')
                ->innerJoin('p.user_info', 'ui')
                ->where('pi.user_info = :userInfoId AND p.bids_due >= :now AND pi.deleted = 0')
                ->orderBy('pi.created_at', 'DESC');

        $params = [
            ':userInfoId' => $userInfoId,
            ':now'        => date('Y-m-d H:i:s'),
        ];
        $q->setParameters($params);

        $query = $q->getQuery();

        return $query->execute();
    }

    /**
     * Get project invites for user count that haven't expired
     * past bids due date
     *
     * @param int $userInfoId
     *
     * @return int
     */
    public function getByUserProjectNotExpiredCount($userInfoId)
    {
        $q = $this->createQueryBuilder('pi')
                ->select('count(pi)')
                ->innerJoin('pi.project', 'p')
                ->where('pi.user_info = :userInfoId AND p.bids_due >= :now AND pi.deleted = 0');

        $params = [
            ':userInfoId' => $userInfoId,
            ':now'        => date('Y-m-d H:i:s'),
        ];
        $q->setParameters($params);

        $query = $q->getQuery();

        return $query->getSingleScalarResult();
    }
}
