<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Type;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RelationEnumType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('choice_label_format')
            ->setAllowedTypes('choice_label_format', ['null', 'string'])
            ->setDefaults([
                'choice_label' => function (Options $options) {
                    return function (RelationType $choice) use ($options) {
                        if (is_string($options['choice_label_format'])) {
                            return sprintf($options['choice_label_format'], strtolower($choice->value));
                        }

                        return $choice->name;
                    };
                },
                'translation_label_format' => null,
            ])
        ;
    }

    public function getParent(): string
    {
        return EnumType::class;
    }
}
