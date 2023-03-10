<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\ModuleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(ManagerRegistry $doctrine, Categorie $categorie = null, Request $request): Response
    {
        // récuperer les categories de la bdd
        $categories = $doctrine->getRepository(Categorie::class)->findAll();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $categorie = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie');
        }
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'formAddCategorie' => $form->createView()
        ]);
    }

    #[Route('/categorie/{id}', name: 'detail_categorie')]
    public function detail(Categorie $categorie, ManagerRegistry $doctrine, Module $module = null, Request $request): Response
    {   
        // $id = $request->attributes->get('_route_params');
        $modules = $categorie->getModules();

        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  
            $module = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('detail_categorie', ['id' => $categorie->getId()]);
        }
        return $this->render('categorie/detail.html.twig', [
            'categorie' => $categorie,
            'modules'=>$modules,
            'formAddModule' => $form->createView()
        ]);
    }

    #[Route('/categorie/{id}/deleteModule/{moduleId}', name: 'delete_module')]
    public function deleteModule(ManagerRegistry $doctrine, Categorie $categorie,ModuleRepository $mr, $moduleId)
    {
        $entityManager = $doctrine->getManager();
        $module = $entityManager->getRepository(Module::class)->find($moduleId);
        $mr->remove($module);
        $entityManager->flush();

        return $this->redirectToRoute('detail_categorie', ['id' => $categorie->getId()]);
    } 
}
