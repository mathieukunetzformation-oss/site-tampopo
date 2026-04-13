<?php

namespace App\Controller;

use App\Entity\ContentBlock;
use App\Form\ContentBlockType;
use App\Repository\ContentBlockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ContentBlockController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/infos', name: 'app_admin_content_block_index', methods: ['GET'])]
    public function index(ContentBlockRepository $blockRepo): Response
    {
        $blocks = $blockRepo->findAll();

        return $this->render('adminInfos.html.twig', [
            'contentBlocks' => $blocks,
        ]);
    }

    // #[Route(name: 'app_content_block_index', methods: ['GET'])]
    // public function index(ContentBlockRepository $contentBlockRepository): Response
    // {
    //     return $this->render('content_block/index.html.twig', [
    //         'content_blocks' => $contentBlockRepository->findAll(),
    //     ]);
    // }

    // #[Route('/new', name: 'app_content_block_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     $contentBlock = new ContentBlock();
    //     $form = $this->createForm(ContentBlockType::class, $contentBlock);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->persist($contentBlock);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_content_block_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('content_block/new.html.twig', [
    //         'content_block' => $contentBlock,
    //         'form' => $form,
    //     ]);
    // }

    // #[Route('/{id}', name: 'app_content_block_show', methods: ['GET'])]
    // public function show(ContentBlock $contentBlock): Response
    // {
    //     return $this->render('content_block/show.html.twig', [
    //         'content_block' => $contentBlock,
    //     ]);
    // }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/infos/edit/{id}', name: 'app_admin_content_block_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SluggerInterface $slugger, ContentBlock $contentBlock, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContentBlockType::class, $contentBlock,  [
            'block_type' => $contentBlock->getType(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = null;
            if ($form->has('photo')) {
                $file = $form->get('photo')->getData();
            }

            if ($file) {
                $safeName = $slugger->slug($contentBlock->getName())->lower();
                $baseName = "photo__info__" . $safeName;

                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/media/photos/infos';


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

                    return $this->redirectToRoute('app_admin_content_block_edit', [
                        'id' => $contentBlock->getId(),
                    ]);
                }
                $contentBlock->setPhoto($fileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_content_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forms/contentBlock/contentBlockEdit.html.twig', [
            'content_block' => $contentBlock,
            'form' => $form,
        ]);
    }

    // #[Route('/{id}', name: 'app_content_block_delete', methods: ['POST'])]
    // public function delete(Request $request, ContentBlock $contentBlock, EntityManagerInterface $entityManager): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$contentBlock->getId(), $request->getPayload()->getString('_token'))) {
    //         $entityManager->remove($contentBlock);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('app_content_block_index', [], Response::HTTP_SEE_OTHER);
    // }
}
