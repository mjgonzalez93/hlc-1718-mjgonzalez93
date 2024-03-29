<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Alquiler;
use AppBundle\Form\Type\AlquilerType;
use AppBundle\Form\Type\VehiculoType;
use AppBundle\Repository\AlquilerRepository;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AlquilerController extends Controller
{
    /**
     * @Route("/alquileres", name="listado_alquiler")
     */
    public function listarAlquilerAction(Request $request)
    {
        if($this->isGranted('ROLE_COMERCIAL')) {
            $alquiler = $this->getDoctrine()->getRepository('AppBundle:Alquiler')->listadoAlquiler();
        }else{
            $usuario = $this->getUser()->getId();
            $alquiler = $this->getDoctrine()->getRepository('AppBundle:Alquiler')->listadoAlquilerCLiente($usuario);
        }
        return $this->render('alquiler/listado.html.twig', [
            'alquiler' => $alquiler,
        ]);

    }

    /**
     * @Route("/alquiler/nuevo", name="nuevo_alquiler")
     */
    public function nuevoAlquilerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $nuevo = true;

        $alquiler = new Alquiler();
        $em->persist($alquiler);

        $form = $this->createForm(AlquilerType::class, $alquiler,['nuevo' => $nuevo]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $dias = $form->get('dias')->getData();
                $vehiculo = $form->get('vehiculo')->getData();
                $precioDia = $vehiculo->getPrecioDia();
                $precioTotal = $dias * $precioDia;
                $usuario = $this->getUser();
                $saldo = $usuario->getSaldo();
                if($saldo<$precioTotal){
                    $this->addFlash('error', 'No tiene suficiente saldo para realizar el alquiler ' );
                    return $this->redirectToRoute('listado_alquiler');
                }else {
                    $alquiler->setUsuario($usuario);
                    $alquiler->setPrecioTotal($precioTotal);
                    $em->flush();
                    $this->addFlash('exito', 'Solicitud realizada correctamente ');
                    return $this->redirectToRoute('listado_alquiler');
                }
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ');
            }
        }

        return $this->render('alquiler/form.html.twig', [
            'alquiler' => $alquiler,
            'formulario' => $form->createView()
        ]);

    }

    /**
     * @Route("/alquiler/editar/{id}", name="edicion_alquiler")
     * @Security("is_granted('ROLE_COMERCIAL')")
     */
    public function alquilerEditarAction(Request $request, Alquiler $id)
    {
        $em = $this->getDoctrine()->getManager();
        $nuevo = false;

        $form = $this->createForm(AlquilerType::class, $id,['nuevo' => $nuevo]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('exito', 'Cambios guardados correctamente ' );
                return $this->redirectToRoute('listado_alquiler');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios ' );
            }
        }

        return $this->render('alquiler/form.html.twig', [
            'alquiler' => $id,
            'formulario' => $form->createView()
        ]);

    }

    /**
     * @Route("/alquiler/resolucion/{id}-{accion}", name="resolucion_alquiler")
     * @Security("is_granted('ROLE_COMERCIAL')")
     */
    public function alquilerResolucionAction(Request $request, Alquiler $id, $accion)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Alquiler|null $alquiler */
        $alquiler = $this->getDoctrine()->getRepository('AppBundle:Alquiler')->find($id);

        try {
            if($accion == "aceptado"){
                $alquiler->setAlquilado(true);
                $usuario = $alquiler->getUsuario();
                $saldo = $usuario->getSaldo();
                $precio = $alquiler->getprecioTotal();
                $usuario->setSaldo($saldo - $precio);
            }elseif($accion = "rechazado"){
                $alquiler->setRechazado(true);
            }

            $em->flush();
            $this->addFlash('exito', 'Cambios guardados correctamente ' );
        }
        catch (\Exception $e) {
            $this->addFlash('error', 'No se han podido guardar los cambios ' );
        }
        return $this->redirectToRoute('listado_alquiler');
    }

    /**
     * @Route("/eliminar/alquiler/{id}", name="eliminar_alquiler")
     * @Security("is_granted('ROLE_COMERCIAL')")
     */
    public function eliminarAlquilerAction(Request $request, Alquiler $alquiler)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            try {
                $em->remove($alquiler);
                $em->flush();
                return $this->redirectToRoute('listado_alquiler');
            }
            catch (\Exception $e) {
                $this->addFlash('error', 'No se ha podido eliminar el vehiculo ');
            }
        }

        return $this->render('alquiler/eliminar.html.twig', [
            'alquiler' => $alquiler
        ]);
    }
}
