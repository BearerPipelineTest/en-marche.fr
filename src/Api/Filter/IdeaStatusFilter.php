<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\IdeaStatusEnum;
use App\Repository\IdeasWorkshop\IdeaRepository;
use Doctrine\ORM\QueryBuilder;

final class IdeaStatusFilter extends AbstractFilter
{
    /**
     * @var IdeaRepository
     */
    private $ideaRepository;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if (Idea::class !== $resourceClass
            || !\array_key_exists($property, $this->properties)
            || !\in_array($value, IdeaStatusEnum::ALL_STATUSES)
        ) {
            return;
        }

        $this->ideaRepository->addStatusFilter($queryBuilder, $queryBuilder->getRootAliases()[0], $value);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description['status'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by status.',
                    'name' => 'status',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    public function setIdeaRepository(IdeaRepository $ideaRepository): void
    {
        $this->ideaRepository = $ideaRepository;
    }
}
