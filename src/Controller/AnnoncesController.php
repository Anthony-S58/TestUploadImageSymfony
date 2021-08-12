<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Images;
use App\Entity\Fichiers;
use App\Form\AnnoncesType;
use App\Repository\AnnoncesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/annonces")
 */
class AnnoncesController extends AbstractController
{
    /**
     * @Route("/", name="annonces_index", methods={"GET"})
     */
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="annonces_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $annonce = new Annonces();
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on récupère les images transmises
            $images = $form->get('images')->getData();

            //on boucle sur les images
            foreach ($images as $image) {
                //on génère un nouveau nom de fichier
                $imgnom = md5(uniqid()) . '.' . $image->guessExtension();

                //on copie le fichier dans le dossier upload
                $image->move(
                    $this->getParameter('images_directory'),
                    $imgnom
                );

                //on stocke l'image dans la base de données (son nom)
                $img = new Images();
                $img->setName($imgnom);
                $annonce->addImage($img);
            }

            //on récupère les fichiers transmis
            $fichiers = $form->get('fichiers')->getData();

            //on boucle sur les fichiers
            foreach ($fichiers as $fichier) {
                //on génère un nouveau nom de fichier
                $fichiername = md5(uniqid()) . '.' . $fichier->guessExtension();

                //on copie le fichier dans le dossier upload
                $fichier->move(
                    $this->getParameter('fichiers_directory'),
                    $fichiername
                );

                //on stocke le fichier dans la base de données (son nom)
                $fich = new Fichiers();
                $fich->setName($fichiername);
                $annonce->addFichier($fich);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="annonces_show", methods={"GET"})
     */
    public function show(Annonces $annonce): Response
    {
        return $this->render('annonces/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="annonces_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Annonces $annonce): Response
    {
        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on récupère les images transmises
            $images = $form->get('images')->getData();

            //on boucle sur les images
            foreach ($images as $image) {
                //on génère un nouveau nom de fichier
                $imgnom = md5(uniqid()) . '.' . $image->guessExtension();

                //on copie le fichier dans le dossier upload
                $image->move(
                    $this->getParameter('images_directory'),
                    $imgnom
                );

                //on stocke l'image dans la base de données (son nom)
                $img = new Images();
                $img->setName($imgnom);
                $annonce->addImage($img);
            }

            //on récupère les fichiers transmis
            $fichiers = $form->get('fichiers')->getData();

            //on boucle sur les fichiers
            foreach ($fichiers as $fichier) {
                //on génère un nouveau nom de fichier
                $fichiername = md5(uniqid()) . '.' . $fichier->guessExtension();

                //on copie le fichier dans le dossier upload
                $fichier->move(
                    $this->getParameter('fichiers_directory'),
                    $fichiername
                );

                //on stocke le fichier dans la base de données (son nom)
                $fich = new Fichiers();
                $fich->setName($fichiername);
                $annonce->addFichier($fich);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="annonces_delete", methods={"POST"})
     */
    public function delete(Request $request, Annonces $annonce): Response
    {
        if ($this->isCsrfTokenValid('delete' . $annonce->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('annonces_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/supprime/image/{id}", name="annonces_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Images $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //on verifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // on récupère le nom ce l'image
            $nom = $image->getName();
            //on supprime le fichier
            unlink($this->getParameter('images_directory') . '/' . $nom);

            //on supprime de la base de données
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            //on répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }

    /**
     * @Route("/supprime/fichier/{id}", name="annonces_delete_fichier", methods={"DELETE"})
     */
    public function deleteFichier(Fichiers $fichier, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //on verifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $fichier->getId(), $data['_token'])) {
            // on récupère le nom du fichiere
            $name = $fichier->getName();
            //on supprime le fichier
            unlink($this->getParameter('fichiers_directory') . '/' . $name);

            //on supprime de la base de données
            $en = $this->getDoctrine()->getManager();
            $en->remove($fichier);
            $en->flush();

            //on répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
