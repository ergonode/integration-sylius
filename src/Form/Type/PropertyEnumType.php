<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class PropertyEnumType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['enum_class', 'choice_label_format']);
        $resolver->setAllowedTypes('enum_class', 'string');
        $resolver->setAllowedTypes('choice_label_format', 'string');

        $resolver->setDefaults([
            'placeholder' => '',
            'required' => false,
            'choices' => function (Options $options): array {
                /** @var class-string<\BackedEnum> $enumClass */
                $enumClass = $options['enum_class'];

                $label = $options['choice_label_format'];
                Assert::string($label);

                return array_combine(
                    array_map(
                        fn (\BackedEnum $case) => sprintf($label, strtolower($case->name)),
                        $enumClass::cases(),
                    ),
                    array_map(
                        fn (\BackedEnum $case) => $case->value,
                        $enumClass::cases(),
                    ),
                );
            },
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
