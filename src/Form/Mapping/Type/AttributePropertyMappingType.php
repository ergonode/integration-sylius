<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Mapping\Type;

use BitBag\SyliusErgonodePlugin\Enum\AttributePropertyMapping;
use BitBag\SyliusErgonodePlugin\Form\Type\PropertyEnumType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class AttributePropertyMappingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('syliusValue', PropertyEnumType::class, [
                'enum_class' => AttributePropertyMapping::class,
                'choice_label_format' => 'bitbag_ergonode.ui.property.attribute.%s',
            ])
            ->add('ergonodeValue', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.ergonode_value',
            ])
        ;
    }
}
