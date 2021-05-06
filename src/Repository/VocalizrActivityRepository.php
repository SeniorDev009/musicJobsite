<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Project;
use App\Entity\UserInfo;
use App\Entity\VocalizrActivity;

/**
 * VocalizrActivityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VocalizrActivityRepository extends EntityRepository
{
    /**
     * @param $user
     * @param array $options
     * @param int   $limit
     * @param int   $first
     *
     * @return array
     */
    public function findActivity($user, $options = [], $limit = 50, $first = 0)
    {
        $q = $this->findActivityQb($user, $options, $limit, $first);
        $query = $q->getQuery();

        $result = $query->getArrayResult();

        return $result;
    }

    /**
     * Find recenty activity within timeframe by type and user
     *
     * @param string $type
     * @param int    $timeAgo    Time ago in seconds
     * @param int    $userInfoId
     *
     * @return VocalizrActivity|null
     */
    public function findRecentyActivityByType($type, $userInfoId = null, $timeAgo = 300)
    {
        $q = $this->createQueryBuilder('va');
        $q->select('va, ui')
            ->leftJoin('va.user_info', 'ui')
            ->where('va.activity_type = :type AND va.created_at >= :timeAgo')
            ->orderBy('va.created_at', 'DESC')
            ->setMaxResults(1);

        $dateTime = new \DateTime();
        $dateTime->modify('-' . $timeAgo . ' seconds');

        $params = [
            ':type'    => $type,
            ':timeAgo' => $dateTime->format('Y-m-d H:i:s'),
        ];

        if ($userInfoId) {
            $q->andWhere('va.user_info = :userInfoId');
            $params[':userInfoId'] = $userInfoId;
        }

        $q->setParameters($params);
        $query = $q->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param UserInfo $userInfo
     */
    public function deleteUserActivity($userInfo)
    {
        $qb = $this->createQueryBuilder('va');
        $qb
            ->select('va.id')
            ->leftJoin('va.project', 'ap')
            ->where('va.actioned_user_info = :user OR va.user_info = :user')
            ->orWhere('ap.user_info = :user OR ap.employee_user_info = :user')

            ->setParameters([
                'user' => $userInfo,
            ])
        ;
        $ids = $qb->getQuery()->execute();
        if (empty($idArray)) {
            return;
        }
        $idArray = [];
        foreach ($ids as $id) {
            $idArray[] = $id['id'];
        }

        $qb = $this->createQueryBuilder('va');
        $qb
            ->delete()
            ->where($qb->expr()->in('va.id', $idArray))
        ;

        $qb->getQuery()->execute();
    }

    /**
     * @param Project $project
     */
    public function deleteForProject(Project $project)
    {
        $qb = $this->createQueryBuilder('va');
        $qb
            ->delete()
            ->andWhere($qb->expr()->eq('va.project', ':project'))
            ->setParameter(':project', $project)
        ;

        $qb->getQuery()->execute();
    }

    /**
     * @param $user
     * @param array $options
     * @param int   $limit
     * @param int   $first
     *
     * @return QueryBuilder
     */
    private function findActivityQb($user, $options = [], $limit = 50, $first = 0)
    {
        $q = $this->createQueryBuilder('va');
        $q->select('va, ui, aui, ap, eui')
            ->leftJoin('va.user_info', 'ui')
            ->leftJoin('va.actioned_user_info', 'aui')
            ->leftJoin('va.project', 'ap')
            ->leftJoin('ap.employee_user_info', 'eui')
            ->setMaxResults($limit)
            ->setFirstResult($first)
            ->where($q->expr()->orX(
                $q->expr()->eq('va.user_info', ':user'),
                $q->expr()->isNull('va.user_info')
            ))
            ->andWhere('aui IS NULL OR aui.is_active = :user_active')
            ->andWhere('eui IS NULL OR eui.is_active = :user_active')
            ->orderBy('va.created_at', 'DESC')
        ;

        $params = [
            ':user'        => $user,
            ':user_active' => true,
        ];
        if (isset($options['lastActivity']) && $options['lastActivity'] > 0) {
            $q->andWhere('va.id > :lastActivity');
            $params[':lastActivity'] = $options['lastActivity'];
        }
        if (isset($options['filter'])) {
            if ($options['filter'] == 'gigs') {
                $q->andWhere('va.activity_type in (:activityType)');
                $params[':activityType'] = [
                    'new_project',
                    'project_completed',
                    'project_awarded',
                ];
            } elseif ($options['filter'] == 'vocalists') {
                $q->andWhere('va.activity_type = :activityType');
                $q->andWhere('va.data like :activityData');
                $params[':activityType'] = 'new_member';
                $params[':activityData'] = '%"is_vocalist":true%';
            } elseif ($options['filter'] == 'producers') {
                $q->andWhere('va.activity_type = :activityType');
                $q->andWhere('va.data like :activityData');
                $params[':activityType'] = 'new_member';
                $params[':activityData'] = '%"is_producer":true%';
            }
        }
        $q->setParameters($params);

        return $q;
    }
}