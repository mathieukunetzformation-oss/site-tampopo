<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Date;

final class ReservationController extends AbstractController
{

    // #[Route('/reservation', name: 'app_reservation_client__new', methods: ['GET'])]
    // public function index(ReservationRepository $reservationRepository): Response
    // {
    //     return $this->render('reservation/index.html.twig', [
    //         'reservations' => $reservationRepository->findAll(),
    //     ]);
    // }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/reservation', name: 'app_admin_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $today = new \DateTime('today'); // today at 00:00

        $todayReservations = $reservationRepository->createQueryBuilder('r')
            ->where('r.date = :today')
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('r.time', 'ASC')
            ->getQuery()
            ->getResult();

        $threshold = new \DateTime('15:00');

        $noonReservations = [];
        $eveningReservations = [];

        foreach ($todayReservations as $reservation) {
            if ($reservation->getTime() < $threshold) {
                $noonReservations[] = $reservation;
            } else {
                $eveningReservations[] = $reservation;
            }
        }

        return $this->render('adminReservation.html.twig', [
            'noonReservations' => $noonReservations,
            'eveningReservations' => $eveningReservations,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/reservation/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $date = $request->query->get('date');   // ?date=x

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Route to JsonResponse for date choice ajax fetch
     */
    #[Route('/admin/reservation/by-date/', name: 'admin_reservation_by_date', methods: ['GET'])]
    public function getReservationsByDate(Request $request, ReservationRepository $repo): JsonResponse
    {
        $date = $request->query->get('date');   // ?date=x

        $reservations = $repo->findBy(
            ['date' => $date],
            ['date' => 'ASC']
        );

        $data = [
            'reservations' => []
        ];

        foreach ($reservations as $reservation) {
            $data['reservations'][] = [
                'id' => $reservation->getId(),
                'firstname' => $reservation->getFirstname(),
                'surname' => $reservation->getSurname(),
                'partySize' => $reservation->getPartySize(),
                'phone' => $reservation->getPhoneNumber(),
                'email' => $reservation->getEmail(),
                'table' => $reservation->getReservedTable(),
            ];
        }

        return $this->json($data);
    }

    // #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    // public function show(Reservation $reservation): Response
    // {
    //     return $this->render('reservation/show.html.twig', [
    //         'reservation' => $reservation,
    //     ]);
    // }

    // #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(ReservationType::class, $reservation);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('reservation/edit.html.twig', [
    //         'reservation' => $reservation,
    //         'form' => $form,
    //     ]);
    // }

    // #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    // public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
    //         $entityManager->remove($reservation);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    // }
}
