<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', null, [
            'label' => 'Gig title',
            'attr'  => [
                'class' => 'form-control',
            ],
        ]);
        $builder->add('description', null, [
            'label' => 'Describe your gig in detail',
            'attr'  => [
                'class' => 'form-control',
            ],
        ]);
        $builder->add('budget_from', MoneyType::class, [
                'label' => 'Budget',
                'attr'  => [
                    'class' => 'inline number'
                ],
            ]);
        $builder->add('budget_to', MoneyType::class, [
                    'label' => 'to',
                    'attr'  => [
                        'class' => 'inline number'
                    ],
            ]);
    }

    public function getName()
    {
        return 'project';
    }
}