<?php

declare(strict_types=1);

namespace App\Identity\Form;

use App\Identity\Dto\UserUpdatePasswordDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserUpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Текущий пароль',
                'attr' => [
                    'class' => 'current-password-input',
                    'placeholder' => 'Пароль',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Пароли не совпадают',
                'first_options' => ['label' => 'Новый пароль'],
                'second_options' => ['label' => 'Повторите пароль'],
                'options' => [
                    'attr' => [
                        'minlength' => 8,
                        'maxlength' => 100,
                        'class' => 'new-password-input',
                        'placeholder' => 'Новый пароль'
                    ],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserUpdatePasswordDto::class,
            'attr' => [
                'class' => 'user-update-password-form',
            ],
        ]);
    }
}
