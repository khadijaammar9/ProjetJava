<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Image;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

class JobController extends AbstractController
{
    #[Route('/job', name: 'app_job')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $job = new Job();
        $job->setType( 'Scrum Masterr');
        $job->setCompany( 'na3na3');
        $job->setDescription ('sabeb');
        $job->setExpiresAt( new \DateTimeImmutable());
        $job->setEmail( 'chafik@gmail.com');
        $image=new Image();
        $image->setUrl('https://cdn.pixabay.com/photo/2015/10/30/10/03/gold-1013618_960_720.jpg ');
        $image->setAlt('job de reves');
        $entityManager->persist($image);
        $job->setImage($image);
        // Création des candidatures
        $candidature1 = new Candidature();
        $candidature2 = new Candidature();
        // Remplir le candidat 1
        $candidature1->setCandidat("Rhaiem");
        $candidature1->setContenu("Formation J2EE");
        $candidature1->setDate(new \DateTime());
        // Remplir le candidat 2
        $candidature2->setCandidat("Salima");
        $candidature2->setContenu("Formation Symfony");
        $candidature2->setDate(new \DateTime());
        // Affecter un Job aux candidatures
        $candidature1->setJob($job);
        $candidature2->setJob($job);
        $entityManager->persist( $job);
        $entityManager->persist($candidature1);
        $entityManager->persist($candidature2);
        $entityManager->flush();
        return $this->render('job/index.html.twig', [
            'id' =>$job->getId(),
        ]);
    }
    #[Route('/job/{id}', name: 'job_show')]
    public function show(EntityManagerInterface $entityManager, $id)
    {
    $job = $entityManager->getRepository(Job::class)->find($id);
    //consulter les candidats
    $listCandidatures=$entityManager->getRepository(Candidature::class)
    ->findBy(['Job'=>$job]);
    if (!$job) {
    throw $this->createNotFoundException(
    'No job found for id '.$id
    );
    }
    return $this->render('job/show.html.twig', [
    'job' =>$job,
    'listCandidatures'=> $listCandidatures,
    ]);
    }
#[Route("/Ajouter", name: "add_candidat")]
public function ajouter_cand(Request $request, EntityManagerInterface $em)
{
    $candidat = new Candidature();
    $fb = $this->createFormBuilder($candidat)
        ->add('candidat', TextType::class)
        ->add('contenu', TextType::class, array("label" => "Contenu"))
        ->add('date', DateType::class)
        ->add('job', EntityType::class, [
            'class' => Job::class,
            'choice_label' => 'type',
        ])
        ->add('Valider', SubmitType::class);
    $form = $fb->getForm();

    // injection dans la base de données
    $form->handleRequest($request);
    if ($form->isSubmitted()) {
        $em->persist($candidat);
        $em->flush();
        return $this->redirectToRoute('number');
    }
    return $this->render('job/ajouter.html.twig', [
    'f' => $form->createView()]);
    
}
#[Route(path: "/add", name: "ajout_job")]
public function ajouter2(Request $request, EntityManagerInterface $em)
{
    $job = new Job();
    $form = $this->createForm("App\Form\JobType", $job);
    $form->handleRequest($request);
    
    if ($form->isSubmitted()) {
        $em->persist($job);
        $em->flush();
        
        return $this->redirectToRoute('Accueil');
    }
    
    return $this->render('job/ajouter.html.twig', 
    ['f' => $form->createView()
    ]);
}

#[Route ("/",name:"home")]
public function  home(Request $request, EntityManagerInterface $em){
    //creation du champ critere
        $form = $this->createFormBuilder()
        ->add("critere",TextType::class) 
        ->add('Valider', SubmitType::class)
        ->getForm();
       $form->handleRequest($request);
        
        $repo = $em->getRepository(Candidature::class);
        $lesCandidats = $repo->findAll();
        // lancer la recherche quand on clique sur le bouton
      if ($form->isSubmitted())
      {
       $data = $form->getData();
       $lesCandidats = $repo->recherche($data['critere']);
      }
        return $this->render('job/home.html.twig',
        ['lesCandidats' => $lesCandidats,'form' =>$form->createView() ]);
       }

#[Route("/supp/{id}", name:"cand_delete")]
public function delete(Request $request, $id,EntityManagerInterface $em): Response
{
    $c =$em->getRepository(Candidature::class)
            ->find($id);
    if (!$c) {
        throw $this->createNotFoundException(
            'No job found for id '.$id
        );
    }
    $em->remove($c);
    $em->flush();
    return $this->redirectToRoute('home');
}

#[Route('/editU/{id}', name: 'edit_user', methods: ['GET', 'POST'])]
public function edit(Request $request, $id, EntityManagerInterface $em)
{$candidat = new Candidature();
$candidat = $em->getRepository(Candidature::class)->find($id);
if (!$candidat) {
throw $this->createNotFoundException(
'No candidat found for id '.$id
);
}
$fb = $this->createFormBuilder($candidat)
->add('candidat', TextType::class)
->add('contenu', TextType::class, array("label" => "Contenu"))
->add('date', DateType::class)
->add('job', EntityType::class, [
'class' => Job::class,
'choice_label' => 'type',
])
->add('Valider', SubmitType::class);
// générer le formulaire à partir du FormBuilder
$form = $fb->getForm();
$form->handleRequest($request);
if ($form->isSubmitted()) {
$em->flush();
return $this->redirectToRoute('home');
}
return $this->render('job/ajouter.html.twig',
['f' => $form->createView()] );
}
}
