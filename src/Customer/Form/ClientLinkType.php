<?php

declare(strict_types=1);

namespace App\Customer\Form;

use App\Customer\Dto\ClientLinkDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ClientLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', TextareaType::class, [
                'label' => 'Заметка о клиенте',
                'required' => false,
            ])
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
            'data_class' => ClientLinkDto::class,
            'attr' => [
                'class' => 'client-link-form',
            ],
        ]);
    }
}
