<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MenuPageRepository;
use App\Repository\MenuProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MainController extends AbstractController
{
    //Route index for shop side
    #[Route('', name: 'app_shop')]
    public function index(MenuProductRepository $productRepo): Response
    {
        $productsWithPhotos = $productRepo->findWithPhotos();

        return $this->render('shopLandingPage.html.twig', [
            "productsWithPhotos" => $productsWithPhotos,
        ]);
    }

    //Client Menu page
    #[Route('/menu', name: 'app_shop_menu', methods: ['GET'])]
    public function shopMenu(MenuPageRepository $menuPageRepository): Response
    {

        return $this->render('shopMenuPage.html.twig', [
            'menuPages' => $menuPageRepository->findBy([], ['pageOrder' => 'ASC']),
        ]);
    }

    //Route index for admin side
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'app_admin')]
    public function adminAccess(): Response
    {
        //additional check for a connected user
        // if (!$this->getUser() instanceof User) {

        //     return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        // }

        return $this->render('adminBase.html.twig', []);
    }
}
