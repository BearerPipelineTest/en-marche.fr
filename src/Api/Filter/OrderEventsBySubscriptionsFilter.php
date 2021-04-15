<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use Doctrine\ORM\QueryBuilder;

final class OrderEventsBySubscriptionsFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'order';
    private const SUB_PROPERTY_NAME = 'subscriptions';

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, BaseEvent::class, true)
            || self::PROPERTY_NAME !== $property
            || !\is_array($value)
            || !\array_key_exists(self::SUB_PROPERTY_NAME, $value)
        ) {
            return;
        }

        $order = \in_array(strtolower($value[self::SUB_PROPERTY_NAME]), ['desc', 'asc']) ? strtolower($value[self::SUB_PROPERTY_NAME]) : 'desc';

        $queryBuilder
            ->addSelect(sprintf(
                '(%s) AS HIDDEN subscriptions_count',
                $queryBuilder->getEntityManager()->createQueryBuilder()
                    ->select('COUNT(1)')
                    ->from(EventRegistration::class, 'event_registration')
                    ->where('event_registration.event = '.$queryBuilder->getRootAliases()[0])
                    ->getDQL()
            ))
            ->addOrderBy('subscriptions_count', $order)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'subscriptions' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
