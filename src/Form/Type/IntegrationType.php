<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class IntegrationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.integration_name',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
            ])
            ->add('apiUrl', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.api_url',
            ])
            ->add('apiKey', PasswordType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.api_key',
            ])
            ->add('categoryTreeCode', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.category_tree_code',
            ])
        ;
    }
}
