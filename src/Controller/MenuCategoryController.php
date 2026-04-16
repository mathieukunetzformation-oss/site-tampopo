<?php

namespace App\Controller;

use App\Entity\MenuCategory;
use App\Form\MenuCategoryType;
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
#[Route('/admin/menu/category')]
final class MenuCategoryController extends AbstractController
{
    /**
     * Route to JsonResponse for choice display ajax fetch
     */
    #[Route('/fetch-by-page/{id}', name: 'admin_categories_by_page', methods: ['GET'])]
    public function getCategoriesByPage(int $id, Request $request, MenuCategoryRepository $repo): JsonResponse
    {
        $categoryId = $request->query->get('categoryId'); // ?categoryId=x

        $categories = $repo->findBy(
            ['page' => $id],
            ['orderInPage' => 'ASC']
        );

        $data = [
            'displayedCategory' => null,
            'categories' => []
        ];

        if ($categoryId) {
            $currentCategory = $repo->find($categoryId);

            if ($currentCategory) {
                $data['displayedCategory'] = [
                    'id' => $currentCategory->getId(),
                    'position' => $currentCategory->getOrderInPage(),
                    'page' => $currentCategory->getPage()->getId(),
                ];
            }
        }

        foreach ($categories as $category) {
            $data['categories'][] = [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
                'position' => $category->getOrderInPage(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Route to new product form
     */
    #[Route('/new', name: 'app_menu_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MenuCategoryRepository $categoryRepo, MenuPageRepository $pageRepo): Response
    {
        $menuCategory = new MenuCategory();
        $pageId = $request->query->get('page');   // ?page=id
        $page = $pageId ? $pageRepo->find($pageId) : $pageRepo->findOneBy(['isProtected' => true]);

        $menuCategory->setTitle('Nouvelle catégorie');
        $menuCategory->setDescription('Ceci est un exemple de description pour la nouvelle catégorie.');
        $menuCategory->setPage($page);
        $menuCategory->setIsProtected(false);


        $form = $this->createForm(MenuCategoryType::class, $menuCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // dd([
            //     'submitted' => $form->isSubmitted(),
            //     'valid' => $form->isValid(),
            //     'data' => $form->getData(),
            //     'errors' => $form->getErrors(true, true),
            // ]);

            if ($form->isValid()) {

                $entityManager->persist($menuCategory);

                $entityManager->flush();

                //reorder the category of the parent page
                $this->reorderCategories(
                    $categoryRepo,
                    $form->get('page')->getData()->getId()
                );

                $entityManager->flush();

                return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('forms/menuCategory/categoryNew.html.twig', [
            'menu_product' => $menuCategory,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_menu_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MenuCategory $menuCategory, MenuCategoryRepository $categoryRepo, EntityManagerInterface $entityManager): Response
    {
        $originalCategoryData = clone $menuCategory; //clone before sub

        $form = $this->createForm(MenuCategoryType::class, $menuCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // dd([
            //     'submitted' => $form->isSubmitted(),
            //     'valid' => $form->isValid(),
            //     'data' => $form->getData(),
            //     'errors' => $form->getErrors(true, true),
            // ]);

            if ($form->isValid()) {

                $entityManager->flush();

                if ($originalCategoryData->getPage()->getId() !== $form->get('page')->getData()->getId()) {
                    $this->reorderCategories(
                        $categoryRepo,
                        $originalCategoryData->getPage()->getId(),
                        $form->get('page')->getData()->getId()
                    );
                } else {
                    $this->reorderCategories(
                        $categoryRepo,
                        $originalCategoryData->getPage()->getId()
                    );
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('forms/menuCategory/categoryEdit.html.twig', [
            'menu_category' => $menuCategory,
            'form' => $form,
        ]);
    }

    /**
     * Reorder categories from one or two pages
     */
    public function reorderCategories(MenuCategoryRepository $categoryRepo, int $initialPageId, ?int $targetPageId = null)
    {

        $categoriesFromInitPage = $categoryRepo->findBy(
            ['page' => $initialPageId],
            ['orderInPage' => 'ASC']
        );

        for ($i = 0; $i < count($categoriesFromInitPage); $i++) {
            $category = $categoriesFromInitPage[$i];
            $category->setOrderInPage(($i + 1) * 10);
        }

        if ($targetPageId) {
            $categoriesFromTargetPage = $categoryRepo->findBy(
                ['page' => $targetPageId],
                ['orderInPage' => 'ASC']
            );

            for ($i = 10; $i < count($categoriesFromTargetPage); $i += 10) {
                $category = $categoriesFromTargetPage[$i];
                $category->setOrderInPage(($i + 1) * 10);
            }
        }
    }


    #[Route('/delete/{id}', name: 'app_menu_category_delete', methods: ['POST'])]
    public function delete(Request $request, MenuCategory $menuCategory, MenuCategoryRepository $categoryRepo, MenuProductRepository $productRepo, EntityManagerInterface $entityManager): Response
    {
        if ($menuCategory->isProtected()) {

            $this->addFlash('error', "Cette catégorie ne peut pas être supprimée car il s'agit de la catégorie de stockage.");
        } else if ($this->isCsrfTokenValid('delete' . $menuCategory->getId(), $request->getPayload()->getString('_token'))) {

            $productsToMove = $productRepo->findBy(
                ['category' => $menuCategory]
            );

            if (count($productsToMove) > 0) {

                $stockCategory = $categoryRepo->findOneBy(
                    ['isProtected' => true]
                ); //find the stock category

                $productsInStock = $productRepo->findBy(
                    ['category' => $stockCategory],
                    ['orderInCategory' => 'ASC']
                );
                $count = count($productsInStock) * 10;

                //move all products of the deleted category to the stock category
                foreach ($productsToMove as $index => $product) {
                    $product->setCategory($stockCategory);
                    $product->setOrderInCategory($count + 10 * ($index + 1));
                }
            }
            $entityManager->remove($menuCategory);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie " . $menuCategory->getTitle() . " a bien été supprimée.");
            if (count($productsToMove) > 0) $this->addFlash('success', "Les produits associés ont été placés dans la catégorie de stockage.");
        } else {
            $this->addFlash('error', "Echec de la suppression : jeton CRSF invalide.");
        }


        return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
    }
}
