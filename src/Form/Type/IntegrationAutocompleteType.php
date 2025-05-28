<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Type;

use BitBag\SyliusErgonodePlugin\Entity\Integration;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Webmozart\Assert\Assert;

#[AsEntityAutocompleteField(
    alias: 'bitbag_ergonode_admin_integration',
    route: 'sylius_admin_entity_autocomplete',
)]
final class IntegrationAutocompleteType extends AbstractType
{
    public const ENTITY_ALIAS = 'entity';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('class', Integration::class);
        $resolver->setDefault('choice_label', 'name');
        $resolver->setDefault('search_fields', ['name']);
        $resolver->setAllowedTypes('search_fields', ['array', 'null']);
        $resolver->setNormalizer('search_fields', fn (Options $options, ?array $fields) => array_map(
            fn (string $field) => self::ENTITY_ALIAS . '.' . $field,
            $fields ?? [],
        ));

        $resolver->setDefault('filter_query', null);
        $resolver->setNormalizer(
            'filter_query',
            fn (Options $options, ?callable $filterQuery) => $this->normalizeFilterQuery($options, $filterQuery),
        );
    }

    public function getBlockPrefix(): string
    {
        return 'bitbag_ergonode_integration_autocomplete';
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

    private function normalizeFilterQuery(Options $options, ?callable $customFilter): callable
    {
        return function (
            QueryBuilder $builder,
            string $query,
            EntityRepository $repository,
        ) use ($options, $customFilter): void {
            $expr = $builder->expr();
            Assert::isArray($options['search_fields']);

            $conditions = self::getComparisons($expr, $options['search_fields']);
            $builder
                ->andWhere($expr->orX(...$conditions))
                ->setParameter('query', '%' . $query . '%');

            if (null !== $customFilter) {
                $customFilter($builder, $query, $repository);
            }
        };
    }

    /**
     * @param array<string> $fields
     *
     * @return iterable<Expr\Comparison>
     */
    private static function getComparisons(Expr $expr, array $fields): iterable
    {
        foreach ($fields as $field) {
            yield $expr->like($field, ':query');
        }
    }
}
