<?php

namespace App\Form;

use App\Entity\Location;
use App\Form\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { 
        $builder
            ->add('city', null, [
                'label' => 'City',
                'attr' => ['autofocus' => true, 'placeholder' => 'Enter city name...'],
            ])
            ->add('country', null, [
                'label' => 'Country',
                'attr' => ['placeholder' => 'Enter 2-letter country code (e.g. PL)'],
            ])
            ->add('latitude', null, [
                'label' => 'Latitude',
                'attr' => ['placeholder' => 'Enter latitude (e.g. 52.2297)'],
            ])
            ->add('longitude', null, [
                'label' => 'Longitude',
                'attr' => ['placeholder' => 'Enter longitude (e.g. 21.0122)'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
