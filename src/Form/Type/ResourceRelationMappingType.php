<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Form\Type;

use BitBag\SyliusErgonodePlugin\Entity\ResourceRelationMappingInterface;
use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Sylius\Bundle\ResourceBundle\Form\Registry\FormTypeRegistryInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Webmozart\Assert\Assert;

final class ResourceRelationMappingType extends AbstractResourceType
{
    public function __construct(
        string $dataClass,
        array $validationGroups,
        protected FormTypeRegistryInterface $formTypeRegistry,
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relationType', RelationEnumType::class, [
                'class' => RelationType::class,
                'choice_label_format' => 'bitbag_ergonode.ui.relation_types.%s',
                'disabled' => true,
            ])
            ->add('integration', IntegrationAutocompleteType::class)
            ->add('importOnce', CheckboxType::class, [
                'label' => 'bitbag_ergonode.ui.import_once',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $mapping = $event->getData();
            $form = $event->getForm();

            if (!$mapping instanceof ResourceRelationMappingInterface) {
                return;
            }

            $type = $mapping->getRelationType()?->value;

            if (null === $type || !$this->formTypeRegistry->has($type, 'mapping')) {
                return;
            }

            $configurationFormType = $this->formTypeRegistry->get($type, 'mapping');

            Assert::notNull($configurationFormType);
            $formFactory = $form->getConfig()->getFormFactory();
            $tempForm = $formFactory->createNamedBuilder('configuration', $configurationFormType);

            foreach ($tempForm->all() as $name => $child) {
                $innerType = $child->getForm()->getConfig()->getType()->getInnerType();

                $form->add($name, get_class($innerType), [
                    ...$child->getForm()->getConfig()->getOptions(),
                ]);
            }
        })
        ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $mapping = $event->getData();
            if (!$mapping instanceof ResourceRelationMappingInterface) {
                return;
            }

            if (!$event->getForm()->has('syliusValue')) {
                return;
            }

            $formSyliusValue = $event->getForm()->get('syliusValue')->getData();

            if (is_object($formSyliusValue) && method_exists($formSyliusValue, 'getCode')) {
                $mapping->setSyliusValue($formSyliusValue->getCode());
            }
        });
    }
}
