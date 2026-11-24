<?php

declare(strict_types=1);

namespace App\Organization\Form;

use App\Customer\Dto\ClientLinkDto;
use App\Organization\Dto\EmployeeLinkDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EmployeeLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Текущий пароль учетной записи',
                'attr' => [
                    'class' => 'current-password-input',
                    'placeholder' => 'Пароль',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmployeeLinkDto::class,
            'attr' => [
                'class' => 'employee-link-form',
            ],
        ]);
    }
}
