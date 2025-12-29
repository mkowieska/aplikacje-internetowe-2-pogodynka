<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Measurement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeasurementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', TextType::class, [
                'label' => 'Date',
                'attr' => ['placeholder' => 'dd.mm.YYYY'],
            ])
            ->add('celsius')
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'id',
            ])
        ;
        $builder->get('date')
            ->addModelTransformer(new CallbackTransformer(
                function ($dateAsDateTime) {
                    if ($dateAsDateTime instanceof \DateTimeInterface) {
                        return $dateAsDateTime->format('d.m.Y');
                    }
                    return '';
                },
                function ($dateAsString) {
                    if (null === $dateAsString || '' === $dateAsString) {
                        return null;
                    }

                    $dt = \DateTime::createFromFormat('d.m.Y', $dateAsString);
                    $errors = \DateTime::getLastErrors();
                    $errorCount = 0;
                    if (is_array($errors) && isset($errors['error_count'])) {
                        $errorCount = (int) $errors['error_count'];
                    }

                    if ($dt === false || $errorCount > 0) {
                        throw new TransformationFailedException('Invalid date format, expected dd.mm.YYYY');
                    }
                    return $dt;
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Measurement::class,
        ]);
    }
}
