<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Type;

use BitBag\SyliusErgonodePlugin\Encryptor\Encryptor;
use BitBag\SyliusErgonodePlugin\Entity\IntegrationInterface;
use Sylius\Bundle\LocaleBundle\Form\Type\LocaleChoiceType;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\ReversedTransformer;

final class IntegrationType extends AbstractResourceType
{
    public function __construct(
        private readonly Encryptor $encryptor,
        private readonly RepositoryInterface $localeRepository,
        string $dataClass,
        array $validationGroups = [],
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

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
                'mapped' => false,
                'help' => 'bitbag_ergonode.ui.api_key_empty_field_help',
            ])
            ->add('categoryTreeCode', TextType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.category_tree_code',
            ])
            ->add('mainLanguageCode', LocaleChoiceType::class, [
                'required' => true,
                'label' => 'bitbag_ergonode.ui.main_language_code',
            ])
            ->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                $apiKey = $form->get('apiKey')->getData();
                if (
                    null !== $apiKey &&
                    is_string($apiKey) &&
                    $data instanceof IntegrationInterface
                ) {
                    $encryptedToken = $this->encryptor->encrypt($apiKey);
                    $data->setApiKey($encryptedToken);
                }
            })
        ;

        $builder
            ->get('mainLanguageCode')->addModelTransformer(
                new ReversedTransformer(new ResourceToIdentifierTransformer($this->localeRepository, 'code')),
            )
        ;
    }
}
