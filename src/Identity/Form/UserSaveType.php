<?php

declare(strict_types=1);

namespace App\Identity\Form;

use App\Identity\Dto\UserSaveDto;
use App\Identity\Enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserSaveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['is_create']) {
            $builder
                ->add('email', EmailType::class, [
                    'label' => 'Почта',
                    'attr' => [
                        'class' => 'email-input',
                        'placeholder' => 'example@gmail.com',
                    ],
                ]);
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'attr' => [
                    'class' => 'firstName-input',
                    'placeholder' => 'Введите имя',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'attr' => [
                    'class' => 'lastName-input',
                    'placeholder' => 'Введите фамилию',
                ],
            ])
            ->add('patronymic', TextType::class, [
                'label' => 'Отчество',
                'required' => false,
                'attr' => [
                    'class' => 'patronymic-input',
                    'placeholder' => 'Введите отчество',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон',
                'attr' => [
                    'class' => 'phone-input',
                    'placeholder' => '+79002554536',
                ],
            ])
            ->add('birthday', DateType::class, [
                'label' => 'Дата рождения',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
                'attr' => [
                    'class' => 'birthday-input',
                ],
            ])
            ->add('gender', EnumType::class, [
                'label' => 'Пол',
                'class' => Gender::class,
                'choice_label' => fn(Gender $gender) => $gender->getTitle(),
                'placeholder' => '- Не выбрано -',
                'required' => false,
                'attr' => [
                    'class' => 'gender-input',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSaveDto::class,
            'is_create' => false,
            'validation_groups' => static fn(FormInterface $form) => $form->getConfig()->getOption('is_create')
                ? ['Default', 'create']
                : ['Default'],
            'attr' => [
                'class' => 'user-save-form',
            ],
        ])
            ->setAllowedTypes('is_create', 'bool');
    }
}
