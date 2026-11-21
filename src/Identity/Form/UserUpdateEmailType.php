<?php

declare(strict_types=1);

namespace App\Identity\Form;

use App\Identity\Dto\UserUpdateEmailDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserUpdateEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Почта',
                'attr' => [
                    'class' => 'email-input',
                    'placeholder' => 'example@gmail.com',
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Текущий пароль',
                'attr' => [
                    'class' => 'current-password-input',
                    'placeholder' => 'Пароль',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserUpdateEmailDto::class,
            'attr' => [
                'class' => 'user-update-email-form',
            ],
        ]);
    }
}
