<?php

namespace MOTO\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MOTO\PrincipalBundle\Form\EmpleadoType;
use MOTO\PrincipalBundle\Entity\Empleado;
use MOTO\PrincipalBundle\Form\EjercicioType;
use MOTO\PrincipalBundle\Entity\Ejercicio;
use MOTO\PrincipalBundle\Form\DietaType;
use MOTO\PrincipalBundle\Entity\Dieta;
use MOTO\PrincipalBundle\Form\TablaType;
use MOTO\PrincipalBundle\Entity\Tablaejercicios;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdministracionController extends Controller {

    public function principalAdministracionAction() {
        session_start();
        if ($_SESSION['resLogin'] == "empleado") {

            $em = $this->getDoctrine()->getEntityManager();
            $consultaEmpleado = "select e from MOTOPrincipalBundle:Empleado e where e.numeroempleado=" . $_SESSION['dni'];
            $queryEmpleado = $em->createQuery($consultaEmpleado);
            $empleados = $queryEmpleado->getResult();

            $dietas = "-";
            $tablas = "-";
            $empleadosAdmin = "-";

            if ($empleados[0] != null) {

                if ($empleados[0]->getPrivilegios() == 1) {
                    $empleadosAdmin = "true";
                }

                if ($empleados[0]->getEspecialidad() == 1) {
                    // Nutricion
                    $dietas = "true";
                } else if ($empleados[0]->getEspecialidad() == 2) {
                    // Entrenamiento
                    $tablas = "true";
                } else if ($empleados[0]->getEspecialidad() == 3) {
                    // Ambas
                    $tablas = "true";
                    $dietas = "true";
                }
            }

            return $this->render('MOTOPrincipalBundle:Administracion:principalAdministracion.html.twig', array("administrador" => "true", "dietas" => $dietas, "tablas" => $tablas, "empleados" => $empleadosAdmin));
        }

        // Si no es un empleado
        $nota = "No tienes permisos para acceder aquí";
        return $this->render('MOTOPrincipalBundle:Administracion:principalAdministracion.html.twig', array("nota" => $nota));
    }

    public function asignarDietaAction() {


        $request = $this->getRequest();
        $session = $request->getSession();

        $em = $this->getDoctrine()->getEntityManager();
        $consultaEmpleado = "select e from MOTOPrincipalBundle:Empleado e where e.numeroempleado=" . $session->get('dni');
        $queryEmpleado = $em->createQuery($consultaEmpleado);
        $empleados = $queryEmpleado->getResult();

        $clientesEmpleado = array();


        foreach ($empleados[0]->getDni() as $cli) {
            $clientesEmpleado[$cli->getDni()] = $cli;
        }

        // Hacer formulario con dos desplegables
        $formClientes = $this->createFormBuilder()
                ->add('cliente', 'choice', array(
                    'choices' => $clientesEmpleado
                ))
                ->add('dieta', 'entity', array(
                    'class' => 'MOTOPrincipalBundle:Dieta'
                ))
                ->getForm();
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {

            $formClientes->bind($request);


            if ($formClientes->isValid()) {

                $em = $this->getDoctrine()->getEntityManager();

                $cliSelect = $formClientes->get("cliente")->getData();
                $dietSelect = $formClientes->get("dieta")->getData();

                $consultaCliente = "select c from MOTOPrincipalBundle:Cliente c where c.dni=" . $cliSelect;
                $queryCliente = $em->createQuery($consultaCliente);
                $clienteMod = $queryCliente->getResult();

                $clienteAModificar = $clienteMod[0];
                $clienteAModificar->setCoddieta($dietSelect);

                $em->persist($clienteAModificar);
                $em->flush();

                return $this->redirect($this->generateUrl('principal_administracion'));
            }
        }

        return $this->render('MOTOPrincipalBundle:Administracion:asignarDieta.html.twig', array("administrador" => "true", 'form' => $formClientes->createView(), 'error' => '-'));
    }

    public function crearDietaAction() {

        $error = "-";
        $request = $this->getRequest();

        $dieta = new Dieta();
        $form = $this->createForm(new DietaType(), $dieta);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                try {
                    $em->persist($dieta);
                    $em->flush();
                } catch (Exception $ex) {
                    $error = "Error al crear dieta";
                    return $this->render('MOTOPrincipalBundle:Administracion:crearDieta.html.twig', array('form' => $form->createView(), 'error' => $error));
                }
                return $this->redirect($this->generateUrl('moto_principal_homepage'));
            }
        }

        return $this->render('MOTOPrincipalBundle:Administracion:crearDieta.html.twig', array('form' => $form->createView(), 'error' => $error));
    }

    public function verDietaAction() {
        // Recuperar dietas
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
                ->add('dieta', 'entity', array(
                    'class' => 'MOTOPrincipalBundle:Dieta'
                ))
                ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $dietaSelec = $form->get("dieta")->getData();

                return $this->render('MOTOPrincipalBundle:Administracion:verDieta.html.twig', array("administrador" => "true", 'dietaMostrar' => $dietaSelec));
            }
        }

        return $this->render('MOTOPrincipalBundle:Administracion:verDieta.html.twig', array("administrador" => "true", 'form' => $form->createView()));
    }

    public function asignarTablaClienteAction() {

        $request = $this->getRequest();
        $session = $request->getSession();

        $em = $this->getDoctrine()->getEntityManager();
        $consultaEmpleado = "select e from MOTOPrincipalBundle:Empleado e where e.numeroempleado=" . $session->get('dni');
        $queryEmpleado = $em->createQuery($consultaEmpleado);
        $empleados = $queryEmpleado->getResult();

        $clientesEmpleado = array();


        foreach ($empleados[0]->getDni() as $cli) {
            $clientesEmpleado[$cli->getDni()] = $cli;
        }


        $formClientes = $this->createFormBuilder()
                ->add('cliente', 'choice', array(
                    'choices' => $clientesEmpleado
                ))
                ->add('tablaejercicios', 'entity', array(
                    'class' => 'MOTOPrincipalBundle:Tablaejercicios'
                ))
                ->getForm();


        if ($request->getMethod() == 'POST') {

            $formClientes->bind($request);


            if ($formClientes->isValid()) {

                $cliSelect = $formClientes->get("cliente")->getData();
                $tabSelect = $formClientes->get("tablaejercicios")->getData();

                $consultaCliente = "select c from MOTOPrincipalBundle:Cliente c where c.dni=" . $cliSelect;
                $queryCliente = $em->createQuery($consultaCliente);
                $clienteMod = $queryCliente->getResult();

                $clienteAModificar = $clienteMod[0];
                $clienteAModificar->setCodtabla($tabSelect);

                $em->persist($clienteAModificar);
                $em->flush();

                return $this->redirect($this->generateUrl('principal_administracion'));
            }
        }

        return $this->render('MOTOPrincipalBundle:Administracion:asignarTablaCliente.html.twig', array("administrador" => "true", 'form' => $formClientes->createView(), 'error' => '-'));
    }

    public function nuevaTablaAction() {
        $error = "-";
        $request = $this->getRequest();

        $tabla = new Tablaejercicios();
        $form = $this->createForm(new TablaType(), $tabla);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                try {
                    $em->persist($tabla);
                    $em->flush();
                } catch (Exception $ex) {
                    $error = "Error al crear tabla de ejercicios";
                    return $this->render('MOTOPrincipalBundle:Administracion:nuevaTabla.html.twig', array('form' => $form->createView(), 'error' => $error));
                }
                return $this->redirect($this->generateUrl('moto_principal_homepage'));
            }
        }

        return $this->render('MOTOPrincipalBundle:Administracion:nuevaTabla.html.twig', array('form' => $form->createView(), 'error' => $error));
    }

    public function nuevaSesionAction() {
        
    }

    public function nuevoEjercicioAction() {

        $error = "-";
        $request = $this->getRequest();

        $ejercicio = new Ejercicio();
        $form = $this->createForm(new EjercicioType(), $ejercicio);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                try {
                    $em->persist($ejercicio);
                    $em->flush();
                } catch (Exception $ex) {
                    $error = "Error al guardar en base de datos";
                    return $this->render('MOTOPrincipalBundle:Administracion:nuevoEjercicio.html.twig', array('form' => $form->createView(), 'error' => $error));
                }
                return $this->redirect($this->generateUrl('moto_principal_homepage'));
            }
        }

        return $this->render('MOTOPrincipalBundle:Administracion:nuevoEjercicio.html.twig', array('form' => $form->createView(), 'error' => $error));
    }

    public function nuevoEmpleadoAction() {

        $error = "-";
        $request = $this->getRequest();

        $empleado = new Empleado();
        $form = $this->createForm(new EmpleadoType(), $empleado);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                try {
                    $em->persist($empleado);
                    $em->flush();
                } catch (Exception $ex) {
                    $error = "Ha habido un problema. ¿El DNI introducido ya existe?";
                    // Mostrar error
                    return $this->render('MOTOPrincipalBundle:Administracion:nuevoEmpleado.html.twig', array('form' => $form->createView(), 'error' => $error));
                }
                // Página principal
                return $this->redirect($this->generateUrl('moto_principal_homepage'));
            }
        }
        // Renderizar formulario
        return $this->render('MOTOPrincipalBundle:Administracion:nuevoEmpleado.html.twig', array('form' => $form->createView(), 'error' => $error));
    }

    public function gestionarEmpleadosAction() {
        
    }

    public function buscarClienteAction() {
        // Desplegable de clientes
        $formClientes = $this->createFormBuilder()
                ->add('cliente', 'entity', array(
                    'class' => 'MOTOPrincipalBundle:Cliente'
                ))
                ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $formClientes->bind($request);

            if ($formClientes->isValid()) {
                $cliSelect = $formClientes->get("cliente")->getData();

                $clienteObtenido = array(
                    'dni' => $cliSelect->getDni(),
                    'nombre' => $cliSelect->getNombre(),
                    'email' => $cliSelect->getEmail(),
                    'direccion' => $cliSelect->getDireccion(),
                    'telefono' => $cliSelect->getTelefono(),
                    'objetivo' => $cliSelect->getObjetivo(),
                    'coddieta' => $cliSelect->getCoddieta(),
                    'codplan' => $cliSelect->getCodplan(),
                    'disponibilidad' => $cliSelect->getDisponibilidad(),
                    'observaciones' => $cliSelect->getObservaciones(),
                    'vencimiento' => $cliSelect->getVencimiento() // NO -> Convertir Date a String
                );

                return $this->render('MOTOPrincipalBundle:Administracion:buscarCliente.html.twig', array('administrador' => 'true', 'cliente' => $cliSelect, 'clienteobtenido' => $clienteObtenido));
            }

            // Si el cliente no se encuentra mensaje
            return $this->render('MOTOPrincipalBundle:Administracion:buscarCliente.html.twig', array('administrador' => 'true', 'error' => 'Cliente no encontrado'));
        }

        return $this->render('MOTOPrincipalBundle:Administracion:buscarCliente.html.twig', array('administrador' => 'true', 'form' => $formClientes->createView(), 'error' => '-'));
    }

}
