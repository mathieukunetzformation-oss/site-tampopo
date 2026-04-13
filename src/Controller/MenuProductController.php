<?php

namespace App\Controller;

use App\Entity\MenuCategory;
use App\Entity\MenuProduct;
use App\Form\MenuProductType;
use App\Repository\MenuCategoryRepository;
use App\Repository\MenuProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/menu/product')]
final class MenuProductController extends AbstractController
{
    /**
     * Route to new product form
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_menu_product_new', methods: ['GET', 'POST'])]
    public function newProduct(Request $request, EntityManagerInterface $entityManager, MenuProductRepository $productRepo, MenuCategoryRepository $categoryRepo): Response
    {
        $menuProduct = new MenuProduct();
        $categoryId = $request->query->get('category');   // ?category=id
        $category = $categoryId ? $categoryRepo->find($categoryId) : $categoryRepo->findOneBy(['isProtected' => true]);
        $menuProduct->setTitle('Nouveau produit');
        $menuProduct->setDescription('Ceci est un exemple de description pour le nouveau produit.');
        $menuProduct->setCategory($category);

        $form = $this->createForm(MenuProductType::class, $menuProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // dd([
            //     'submitted' => $form->isSubmitted(),
            //     'valid' => $form->isValid(),
            //     'data' => $form->getData(),
            //     'errors' => $form->getErrors(true, true),
            // ]);

            if ($form->isValid()) {
                $file = $form->get('photo')->getData();

                if ($file) {

                    $baseName = "photo__produit__" . $menuProduct->getId();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/media/photos/produits';

                    $existingFiles = glob($uploadDir . '/' . $baseName . '.*') ?: []; // find all files with same base name but any extension

                    foreach ($existingFiles as $existingFile) {
                        if (file_exists($existingFile)) {
                            unlink($existingFile); // delete all variants (.jpg, .png, etc.)
                        }
                    }

                    $fileName = $baseName . "." . $file->guessExtension();

                    try {
                        $file->move($uploadDir, $fileName);
                    } catch (FileException $e) {
                        $this->addFlash('error', "Erreur lors de l'upload du fichier : " . $e->getMessage());

                        return $this->redirectToRoute('app_menu_product_new', [
                            'id' => $menuProduct->getId(),
                        ]);
                    }

                    $menuProduct->setPhoto($fileName);
                }

                $entityManager->persist($menuProduct);

                $entityManager->flush();

                $this->reorderProducts(
                    $productRepo,
                    $form->get('category')->getData()->getId()
                );

                $entityManager->flush();

                return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('forms/menuProduct/productNew.html.twig', [
            'menu_product' => $menuProduct,
            'form' => $form,
        ]);
    }

    /**
     * Route to edit product form
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_menu_product_edit', methods: ['GET', 'POST'])]
    public function editProduct(Request $request, MenuProduct $menuProduct, MenuProductRepository $productRepo, EntityManagerInterface $entityManager): Response
    {
        $originalProductData = clone $menuProduct; //before sub

        $form = $this->createForm(MenuProductType::class, $menuProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // dd([
            //     'submitted' => $form->isSubmitted(),
            //     'valid' => $form->isValid(),
            //     'data' => $form->getData(),
            //     'errors' => $form->getErrors(true, true),
            //     'choiceViews' => $form->get('orderInCategory')->createView()->vars['choices'],
            // ]);

            if ($form->isValid()) {
                //photo
                $file = $form->get('photo')->getData();
                $id = $form->getData()->getId();

                if ($file) {

                    $baseName = "photo__produit__" . $menuProduct->getId();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/media/photos/products';

                    $existingFiles = glob($uploadDir . '/' . $baseName . '.*') ?: []; // find all files with same base name but any extension

                    foreach ($existingFiles as $existingFile) {
                        if (file_exists($existingFile)) {
                            unlink($existingFile); // delete all variants (.jpg, .png, etc.)
                        }
                    }

                    $fileName = $baseName . "." . $file->guessExtension();

                    try {
                        $file->move($uploadDir, $fileName);
                    } catch (FileException $e) {
                        $this->addFlash('error', "Erreur lors de l'upload du fichier : " . $e->getMessage());

                        return $this->redirectToRoute('app_menu_product_edit', [
                            'id' => $menuProduct->getId(),
                        ]);
                    }

                    $file->move($uploadDir, $fileName);
                    $menuProduct->setPhoto($fileName);
                }

                $entityManager->flush();

                if ($originalProductData->getCategory()->getId() !== $form->get('category')->getData()->getId()) {
                    $this->reorderProducts(
                        $productRepo,
                        $originalProductData->getCategory()->getId(),
                        $form->get('category')->getData()->getId()
                    );
                } else {
                    $this->reorderProducts(
                        $productRepo,
                        $originalProductData->getCategory()->getId()
                    );
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('forms/menuProduct/productEdit.html.twig', [
            'menu_product' => $menuProduct,
            'form' => $form,
        ]);
    }

    /**
     * Reorder products from one or two categories
     */
    #[IsGranted('ROLE_ADMIN')]
    public function reorderProducts(MenuProductRepository $productRepo, int $initialCategoryId, ?int $targetCategoryId = null)
    {

        $productsFromInitCategory = $productRepo->findBy(
            ['category' => $initialCategoryId],
            ['orderInCategory' => 'ASC']
        );


        for ($i = 0; $i < count($productsFromInitCategory); $i++) {
            $product = $productsFromInitCategory[$i];
            $product->setOrderInCategory(($i + 1) * 10);
        }


        if ($targetCategoryId) {
            $productsFromTargetCategory = $productRepo->findBy(
                ['category' => $targetCategoryId],
                ['orderInCategory' => 'ASC']
            );

            for ($i = 10; $i < count($productsFromTargetCategory); $i += 10) {
                $product = $productsFromTargetCategory[$i];
                $product->setOrderInCategory(($i + 1) * 10);
            }
        }
    }


    /**
     * Route to delete product form
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'app_menu_product_delete', methods: ['POST'])]
    public function deleteProduct(Request $request, MenuProduct $menuProduct, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $menuProduct->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($menuProduct);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_adminMenuPage', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Route to JsonResponse for choice display ajax fetch
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/fetch-by-category/{id}', name: 'admin_products_by_category', methods: ['GET'])]
    public function getProductsByCategory(int $id, Request $request, MenuProductRepository $repo): JsonResponse
    {
        $productId = $request->query->get('productId');

        $products = $repo->findBy(
            ['category' => $id],
            ['orderInCategory' => 'ASC']
        );

        $currentProduct = $repo->find($productId);

        $data = [
            'current' => null,
            'products' => []
        ];

        if ($productId) {
            $currentProduct = $repo->find($productId);

            if ($currentProduct) {
                $data['current'] = [
                    'id' => $currentProduct->getId(),
                    'position' => $currentProduct->getOrderInCategory(),
                    'category' => $currentProduct->getCategory()->getId(),
                ];
            }
        }

        foreach ($products as $product) {
            $data['products'][] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'position' => $product->getOrderInCategory(),
            ];
        }

        return $this->json($data);
    }

    // /**
    //  * Route to JsonResponse for product grid ajax fetch
    //  */
    // #[Route('/fetch-all-products-with-photo', name: 'fetch_products_with_photos', methods: ['GET'])]
    // public function getProductsWithPhotos(Request $request, MenuProductRepository $repo): JsonResponse
    // {

    //     $products = $repo->createQueryBuilder('p')
    //         ->where('p.photo IS NOT NULL')
    //         ->andWhere('p.photo != \'\'') // we add that to exclude empty strings
    //         ->getQuery()
    //         ->getResult();

    //     $data = [
    //         'products' => []
    //     ];

    //     foreach ($products as $product) {
    //         $data['products'][] = [
    //             'photo' => $product->getPhoto(),
    //             'title' => $product->getTitle(),
    //             'description' => $product->getDescription(),
    //         ];
    //     }

    //     return $this->json($data);
    // }
}
