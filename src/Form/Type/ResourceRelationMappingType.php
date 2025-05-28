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
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ResourceRelationMappingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relationType', RelationEnumType::class, [
                'class' => RelationType::class,
                'choice_label_format' => 'bitbag_ergonode.ui.relation_types.%s',
            ])
            ->add('integration', IntegrationAutocompleteType::class)
            ->add('ergonodeValue', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.ergonode_value',
            ])
            ->add('syliusValue', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.sylius_value',
            ])
            ->add('importOnce', CheckboxType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.import_once',
            ])
        ;
    }
}
