<?php

namespace UserBundle\Controller;

use UserBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use UserBundle\Form\RoleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class RoleController extends Controller
{
    /**
     * @Route("/user/rol", name="user_rol")
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function indexAction()
    {
        $em = $this->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('UserBundle:Role');
        $data = $repository->findAll();
        $form = $this->get('form.factory')->create(new RoleType());
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('UserBundle:Role:index.html.twig', array('roles' => $data,'form'=>$form->createView(),'id'=>'rol')),
            'sms' => 'Vista del listado de roles'
        ]);
    }

    /**
     * @Route("/user/save_rol", name="user_save_rol")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveRolAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $roleRepository = $em->getRepository('UserBundle:Role');

            $role = new Role();

            $form = $this->get('form.factory')->create(new RoleType(), $role);
            $form->handleRequest($request);

            if($form->isValid()){
                $roleRepository->save($role);
                return new JsonResponse(['success' => true, 'msg' => 'Adicionado correctamente']);
            }

            return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/user/edit_rol", name = "user_edit_rol")
     * @param Request $request
     * @return JsonResponse
     */
    public function editRolAction(Request $request){
        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $roleRepository = $em->getRepository('UserBundle:Role');

            $id = $request->request->get('id');
            $oldRole = $roleRepository->findById($id)[0];

            $form = $this->get('form.factory')->create(new RoleType(), $oldRole);
            $form->handleRequest($request);

            if($form->isValid()){
                $roleRepository->save($oldRole);
                return new JsonResponse(['success' => true, 'msg' => 'Editado correctamente']);
            }

            return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/user/{id}/delete_rol", name = "user_delete_rol")
     * @ParamConverter("rol", class="UserBundle\Entity\Role")
     * @param Role $rol
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteRolAction(Role $rol){

        $em = $this->get('doctrine')->getEntityManager();
        $roleRepository = $em->getRepository('UserBundle:Role');

        $roleRepository->delete($rol);

        return new JsonResponse(['success' => true, 'msg' => 'Eliminado incorrectamente']);
    }
}
