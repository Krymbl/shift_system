<?php

declare(strict_types=1);

namespace App\Customer\Form;

use App\Customer\Dto\ClientSaveDto;
use App\Identity\Form\UserSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


final class ClientSaveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('userSaveDto', UserSaveType::class, [
                'mode' => $options['mode'],
                'label' => false,
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Заметка о клиенте',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientSaveDto::class,
            'mode' => 'edit',
            'validation_groups' => static fn (Options $options): array =>
            $options['mode'] === 'create'
                ? ['Default', 'create']
                : ['Default'],
            'attr' => [
                'class' => 'client-save-form',
            ],
        ])->setAllowedValues('mode', ['create', 'edit']);
    }
}
