<?php

namespace App\Controller;

use App\Entity\Provincia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ContactoFormType as ContactoType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Contacto;
use Doctrine\Persistence\ManagerRegistry;

final class ContactoController extends AbstractController
{
    #[Route('/contacto/update/{id}/{nombre}', name: 'modificar_contacto')]
    public function update(ManagerRegistry $doctrine, $id, $nombre): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);

        if ($contacto) {
            $contacto->setNombre($nombre);

            try
            {
                $entityManager->flush();
                return $this->render('ficha_contacto.html.twig', [
                    'contacto' => $contacto
                ]);
            } catch (\Exception $e) {
                return new Response("Error insertando objetos");
            }
        } else {
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => null
            ]);
        }
    }
    #[Route('/contacto/delete/{id}', name: 'eliminar_contacto')]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);

        if ($contacto) {
            try {
                $entityManager->remove($contacto);
                $entityManager->flush();
                return new Response("Contacto eliminado");
            } catch (\Exception $e) {
                return new Response("Error eliminado objeto");
            }
        } else {
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => null
            ]);
        }
    }
    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request) {
        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $contacto = $formulario->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
        }
        return $this->render('nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }
    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements:["codigo"=>"\d+"])]
    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo) {
        $repositorio = $doctrine->getRepository(Contacto::class);
        //En este caso, los datos los obtenemos del repositorio de contactos
        $contacto = $repositorio->find($codigo);
        if ($contacto){
            $formulario = $this->createForm(ContactoType::class, $contacto);

            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                //Esta parte es igual que en la ruta para insertar
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
            }
            return $this->render('nuevo.html.twig', array(
                'formulario' => $formulario->createView()
            ));
        }else{
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => NULL
            ]);
        }
    }

    #[Route('/contacto/{codigo?1}', name: 'ficha_contacto')]
    public function ficha(ManagerRegistry $doctrine, $codigo): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);

        $contactos = $repositorio->findByName($codigo);

        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contactos
        ]);
    }
}
