<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalaireInputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contractType', 
        ChoiceType::class, [
                'choices'  => [
                    'CDI' => 'permanent',
                    'CDD' => 'fixed_term',
                    'Apprentissage' => 'apprenticeship',
                    'Stage' => 'internship',
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => 'permanent',
            ])
            ->add('grossSalary', NumberType::class, ['scale' => 2])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}