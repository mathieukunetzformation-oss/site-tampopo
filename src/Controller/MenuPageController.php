<?php

namespace App\Controller;

use App\Entity\MenuPage;
use App\Form\MenuPageType;
use App\Repository\MenuCategoryRepository;
use App\Repository\MenuPageRepository;
use App\Repository\MenuProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/menu')]
final class MenuPageController extends AbstractController
{

    /**
     * Route to dashboard Menu page
     */
    #[Route('', name: 'app_adminMenuPage', methods: ['GET'])]
    public function indexAdmin(MenuPageRepository $menuPageRepository): Response
    {
        return $this->render('adminMenu.html.twig', [
            'menuPages' => $menuPageRepository->findBy([], ['pageOrder' => 'ASC']),
        ]);
    }

    /**
     * Route to reorder elements position upon AJAX fetch
     */
    #[Route('/reorder', name: 'admin_menu_reorder', methods: ['POST'])]
    public function reorderMenu(
        Request $request,
        EntityManagerInterface $em,
        MenuPageRepository $pageRepo,
        MenuCategoryRepository $categoryRepo,
        MenuProductRepository $productRepo
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $token = $data['csrfToken'] ?? null;

        if (!$this->isCsrfTokenValid('menu_reorder', $token)) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $pages = $data['pages'] ?? [];
        $categories = $data['categories'] ?? [];
        $products = $data['products'] ?? [];

        // Update pages
        foreach ($pages as $pageData) {

            $page = $pageRepo->find($pageData['id']);

            if ($page) {
                $page->setPageOrder($pageData['position']);
            }
        }

        // Update categories
        foreach ($categories as $categoryData) {

            $category = $categoryRepo->find($categoryData['id']);

            if ($category) {

                $category->setOrderInPage($categoryData['position']);

                $page = $pageRepo->find($categoryData['page']);

                if ($page) {
                    $category->setPage($page);
                }
            }
        }

        // Update products
        foreach ($products as $productData) {

            $product = $productRepo->find($productData['id']);

            if ($product) {

                $product->setOrderInCategory($productData['position']);

                $category = $categoryRepo->find($productData['category']);

                if ($category) {
                    $product->setCategory($category);
                }
            }
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'updated' => [
                'pages' => count($pages),
                'categories' => count($categories),
                'products' => count($products)
            ]
        ]);
    }

    #[Route('/page/fetch-all', name: 'admin_fetch_all_pages', methods: ['GET'])]
    public function getAllPages(Request $request, MenuPageRepository $pageRepo): JsonResponse
    {
        $pageId = $request->query->get('pageId'); // ?pageId=x

        $pages = $pageRepo->findBy([], ['pageOrder' => 'ASC']);

        $data = [
            'displayedPage' => null,
            'pages' => []
        ];

        foreach ($pages as $page) {
            $data['pages'][] = [
                'id' => $page->getId(),
                'position' => $page->getPageOrder(),
                'title' => $page->getTitle(),
            ];
        }

        if ($pageId) {
            $currentPage = $pageRepo->find($pageId);

            if ($currentPage) {
                $data['displayedPage'] = [
                    'id' => $currentPage->getId(),
                    'position' => $currentPage->getPageOrder(),
                    'title' => $currentPage->getTitle(),
                ];
            }
        }


        return $this->json($data);
    }

    #[Route('/page/new', name: 'app_menu_page_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MenuPageRepository $pageRepo): Response
    {
        $menuPage = new MenuPage();
        $menuPage->setTitle('Nouvelle page');
        $menuPage->setDescription('Ceci est un exemple de description pour la nouvelle page.');
        $menuPage->setIsProtected(false);
        $form = $this->createForm(MenuPageType::class, $menuPage);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $entityManager->persist($menuPage);
                $entityManager->flush();

                $this->reorderPages($pageRepo);
                $entityManager->flush();

                return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('forms/menuPage/pageNew.html.twig', [
            'menu_page' => $menuPage,
            'form' => $form,
        ]);
    }

    #[Route('/page/{id}/edit', name: 'app_menu_page_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MenuPage $menuPage, MenuPageRepository $pageRepo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuPageType::class, $menuPage);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // dd([
            //     'submitted' => $form->isSubmitted(),
            //     'valid' => $form->isValid(),
            //     'data' => $form->getData(),
            //     'errors' => $form->getErrors(true, true),
            //     'choiceViews' => $form->get('pageOrder')->createView()->vars['choices'],

            // ]);

            if ($form->isValid()) {
                $entityManager->flush();

                $this->reorderPages($pageRepo);
                $entityManager->flush();

                return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('forms/menuPage/pageEdit.html.twig', [
            'menu_page' => $menuPage,
            'form' => $form,
        ]);
    }

    /**
     * Reorder categories from one or two pages
     */
    public function reorderPages(MenuPageRepository $pageRepo)
    {
        $pages = $pageRepo->findBy(
            [],
            ['pageOrder' => 'ASC']
        );

        for ($i = 0; $i < count($pages); $i++) {
            $page = $pages[$i];
            $page->setPageOrder(($i + 1) * 10);
        }
    }

    #[Route('/page/delete/{id}', name: 'app_menu_page_delete', methods: ['POST'])]
    public function delete(Request $request, MenuPage $menuPage, MenuPageRepository $pageRepo, MenuCategoryRepository $categoryRepo, EntityManagerInterface $entityManager): Response
    {
        if ($menuPage->isProtected()) {

            $this->addFlash('error', "Cette page ne peut pas être supprimée car il s'agit de la page de stockage.");
        } else if ($this->isCsrfTokenValid('delete' . $menuPage->getId(), $request->getPayload()->getString('_token'))) {

            $categoriesToMove = $categoryRepo->findBy(
                ['page' => $menuPage]
            );

            if (count($categoriesToMove) > 0) {

                $stockPage = $pageRepo->findOneBy(
                    ['isProtected' => true]
                ); //find the stock page

                $categoriesInStock = $categoryRepo->findBy(
                    ['page' => $stockPage],
                    ['orderInPage' => 'ASC']
                );
                $count = count($categoriesInStock) * 10;

                //move all categories of the deleted page to the stock page
                foreach ($categoriesToMove as $index => $category) {
                    $category->setPage($stockPage);
                    $category->setOrderInPage($count + 10 * ($index + 1));
                }
            }

            $entityManager->remove($menuPage);
            $entityManager->flush();

            $this->addFlash('success', "La page " . $menuPage->getTitle() . " a bien été supprimée.");
            if (count($categoriesToMove) > 0) $this->addFlash('success', "Les catégories associées ont été placées dans la page de stockage.");
        } else {
            $this->addFlash('error', "Echec de la suppression : jeton CRSF invalide.");
        }

        return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
    }
}
