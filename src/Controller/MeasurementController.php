<?php

namespace App\Controller;

use App\Entity\Measurement;
use App\Form\MeasurementType;
use App\Repository\MeasurementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/measurement')]
final class MeasurementController extends AbstractController
{
    #[Route(name: 'app_measurement_index', methods: ['GET'])]
    public function index(MeasurementRepository $measurementRepository): Response
    {
        return $this->render('measurement/index.html.twig', [
            'measurements' => $measurementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_measurement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $measurement = new Measurement();
        $form = $this->createForm(MeasurementType::class, $measurement, [
            'validation_groups' => 'create',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Manual guarded validation for celsius to ensure numeric and within allowed range
            // (some form types can submit strings; enforce numeric range here and attach a FormError)
            try {
                $celsiusValue = $measurement->getCelsius();
                if (null !== $celsiusValue && $celsiusValue !== '') {
                    // allow both string and numeric, cast to float for checks
                    if (!is_numeric($celsiusValue)) {
                        $form->get('celsius')->addError(new FormError('Celsius must be a number.'));
                    } else {
                        $num = (float) $celsiusValue;
                        if ($num < -50 || $num > 50) {
                            $form->get('celsius')->addError(new FormError('Celsius must be between -50 and 50.'));
                        }
                    }
                }
            } catch (\Exception $e) {
                // if anything goes wrong reading value, attach generic error
                if ($form->has('celsius')) {
                    $form->get('celsius')->addError(new FormError('Invalid celsius value.'));
                }
            }

            // run validator explicitly in the 'create' group and map violations to the form so backend errors are visible
            $violations = $validator->validate($measurement, null, ['create']);
            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $propertyPath = $violation->getPropertyPath();
                    // try to attach violation to the specific field if present, otherwise attach to the form root
                    if ($propertyPath && $form->has($propertyPath)) {
                        $form->get($propertyPath)->addError(new FormError($violation->getMessage()));
                    } else {
                        $form->addError(new FormError($violation->getMessage()));
                    }
                }
            }

                // explicit celsius range check to guarantee controller-side protection
                $hasRangeError = false;
                $celsius = $measurement->getCelsius();
                if ($celsius !== null && $celsius !== '') {
                    // allow numeric-like strings too
                    if (!is_numeric($celsius) || $celsius < -50 || $celsius > 50) {
                        if ($form->has('celsius')) {
                            $form->get('celsius')->addError(new FormError('Celsius must be between -50 and 50.'));
                        } else {
                            $form->addError(new FormError('Celsius must be between -50 and 50.'));
                        }
                        $hasRangeError = true;
                    }
                }

            // Only persist if both the form is valid and there are no validator violations
                // Only persist if the form is valid, there are zero validator violations and no explicit range error
                if ($form->isValid() && count($violations) === 0 && $hasRangeError === false) {
                $entityManager->persist($measurement);
                $entityManager->flush();

                return $this->redirectToRoute('app_measurement_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('measurement/new.html.twig', [
            'measurement' => $measurement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_measurement_show', methods: ['GET'])]
    public function show(Measurement $measurement): Response
    {
        return $this->render('measurement/show.html.twig', [
            'measurement' => $measurement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_measurement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Measurement $measurement, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(MeasurementType::class, $measurement, [
            'validation_groups' => 'create',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $violations = $validator->validate($measurement, null, ['create']);
            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $propertyPath = $violation->getPropertyPath();
                    if ($propertyPath && $form->has($propertyPath)) {
                        $form->get($propertyPath)->addError(new FormError($violation->getMessage()));
                    } else {
                        $form->addError(new FormError($violation->getMessage()));
                    }
                }
            }

                // explicit celsius range check to guarantee controller-side protection in edit
                $hasRangeError = false;
                $celsius = $measurement->getCelsius();
                if ($celsius !== null && $celsius !== '') {
                    if (!is_numeric($celsius) || $celsius < -50 || $celsius > 50) {
                        if ($form->has('celsius')) {
                            $form->get('celsius')->addError(new FormError('Celsius must be between -50 and 50.'));
                        } else {
                            $form->addError(new FormError('Celsius must be between -50 and 50.'));
                        }
                        $hasRangeError = true;
                    }
                }

            // Only flush if both the form is valid and there are no validator violations
                // Only flush if the form is valid, there are zero validator violations and no explicit range error
                if ($form->isValid() && count($violations) === 0 && $hasRangeError === false) {
                $entityManager->flush();

                return $this->redirectToRoute('app_measurement_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('measurement/edit.html.twig', [
            'measurement' => $measurement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_measurement_delete', methods: ['POST'])]
    public function delete(Request $request, Measurement $measurement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$measurement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($measurement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_measurement_index', [], Response::HTTP_SEE_OTHER);
    }
}
