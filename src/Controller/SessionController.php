<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Session;
use App\Entity\Programme;
use App\Entity\Stagiaire;
use App\Form\SessionType;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\StagiaireRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    
    public function index(ManagerRegistry $doctrine, SessionRepository $sr, Session $session = null, Request $request): Response
    {
        $today = date('d/m/Y');
        // Récuperer les sessions en base de données
        $pastSessions = $sr->findPastSessions();
        $currentSessions = $sr->findCurrentSessions();
        $upcomingSessions = $sr->findUpcomingSessions();

        $sessions = $doctrine->getRepository(Session::class)->findAll();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $session = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('app_session');
        }
        return $this->render('session/index.html.twig', [
            'today' => $today,
            'pastSessions' => $pastSessions,
            'currentSessions' => $currentSessions,
            'upcomingSessions' => $upcomingSessions,
            'formAddSession' => $form->createView(),
        ]);
    }


    #[Route('/session/{id}', name: 'detail_session')]

    public function detail(Session $session, StagiaireRepository $sr, ModuleRepository $mr): Response
    {
        $session_id = $session->getId();
        $stagiairesInscrits = $session->getStagiaires();
        $nomSession = $session->getNomSession();
        $programmes = $session->getProgrammes();
        $modulesLibres = $mr->findModulesLibresBySessionId($session_id);
        $stagiairesNonInscrits = $sr->findStagiairesNonInscritsBySessionId($session_id);   
        return $this->render('session/detail.html.twig', [
            'stagiairesInscrits' => $stagiairesInscrits,
            'programmes' => $programmes,
            'modulesLibres' => $modulesLibres,
            'stagiairesNonInscrits' => $stagiairesNonInscrits,  
            'session' => $session        
        ]);
    }


    #[Route('/session/{id}/addProgramme/{moduleId}', name: 'add_programme')]
    public function addProgramme(ManagerRegistry $doctrine, Session $session, $moduleId)
    {
        if(isset($_POST['submitProgramme'])){
            $duree =filter_input(INPUT_POST,"duree",FILTER_VALIDATE_INT);
            if($duree > 0){
            $entityManager = $doctrine->getManager();
            $module = $entityManager->getRepository(Module::class)->find($moduleId);
            $programme = new Programme();
            $programme->setDureeProgramme($duree);
            $programme->setModule($module);
            $programme->setSession($session);
            $entityManager->persist($programme);
            $entityManager->flush();
            return $this->redirectToRoute('detail_session', ['id' => $session->getId()]);
            }
     
            else {
        return $this->redirectToRoute('detail_session', ['id' => $session->getId()]);
            }
        }
    }


    #[Route('/session/{id}/deleteProgramme/{programmeId}', name: 'delete_programme')]
    public function deleteProgramme(ManagerRegistry $doctrine, Session $session,ProgrammeRepository $pr, $programmeId)
    {
        $entityManager = $doctrine->getManager();
        $programme = $entityManager->getRepository(Programme::class)->find($programmeId);
        $pr->remove($programme);
        $entityManager->flush();

        return $this->redirectToRoute('detail_session', ['id' => $session->getId()]);
    } 


    #[Route('/session/{id}/inscrire/{stagiaireId}', name: 'inscrire_stagiaire')]

    public function inscrireStagiaire(ManagerRegistry $doctrine, Session $session, $stagiaireId)
    {
        $entityManager = $doctrine->getManager();
        $stagiaire = $entityManager->getRepository(Stagiaire::class)->find($stagiaireId);
        $session->addStagiaire($stagiaire);
        $entityManager->flush();

        return $this->redirectToRoute('detail_session', ['id' => $session->getId()]);
    }


    #[Route('/session/{id}/desinscrire/{stagiaireId}', name: 'desinscrire_stagiaire')]

    public function desinscrireStagiaire(ManagerRegistry $doctrine, Session $session, $stagiaireId)
    {
        $entityManager = $doctrine->getManager();
        $stagiaire = $entityManager->getRepository(Stagiaire::class)->find($stagiaireId);
        $session->removeStagiaire($stagiaire);
        $entityManager->flush();

        return $this->redirectToRoute('detail_session', ['id' => $session->getId()]);
    }


    #[Route('/session/{id}/delete', name: 'delete_session')]

    public function delete(ManagerRegistry $doctrine, Session $session) {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($session);
        $entityManager->flush();

        return $this->redirectToRoute('app_session');
    }
}
