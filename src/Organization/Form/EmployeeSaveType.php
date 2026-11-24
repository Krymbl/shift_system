<?php

declare(strict_types=1);

namespace App\Organization\Form;

use App\Customer\Dto\ClientSaveDto;
use App\Identity\Form\UserSaveType;
use App\Organization\Dto\EmployeeSaveDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


final class EmployeeSaveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('userSaveDto', UserSaveType::class, [
                'mode' => $options['mode'],
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmployeeSaveDto::class,
            'mode' => 'edit',
            'validation_groups' => static fn (Options $options): array =>
            $options['mode'] === 'create'
                ? ['Default', 'create']
                : ['Default'],
            'attr' => [
                'class' => 'employee-save-form',
            ],
        ])->setAllowedValues('mode', ['create', 'edit']);
    }
}
