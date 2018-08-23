<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class UserController extends Controller
{
    /**
     * @Route("/user/user", name="user_user")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->get('doctrine')->getEntityManager();
        $userRepository = $em->getRepository('UserBundle:User');
        $users = $userRepository->findAll();
        $roleRepository = $em->getRepository('UserBundle:Role');
        $roles = $roleRepository->findAll();
        $form = $this->container->get('fos_user.registration.form');
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('UserBundle:User:index.html.twig', array('data' => $users,'form'=>$form->createView(),'roles'=>$roles,'id'=>'user')),
            'sms' => 'Vista del listado de usuarios'
        ]);
    }

    /**
     * @Route("/user/save_user", name="user_save_user")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request){
        $em = $this->get('doctrine')->getEntityManager();
        $user = new User();
        $user->setUsername($request->request->get('username'));
        $user->setEmail($request->request->get('email'));
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user,$request->request->get('plainPassword1'));
        $user->setPassword($encoded);
        $user->setEnabled(true);
        $roles=$request->request->get('role');
        ($roles != '') ? ($user->setRoles($roles)) : ($user->setRoles(array()));
        $em->persist($user);
        $em->flush();
        return new JsonResponse(['success' => true, 'sms' => 'El usuario se ha adicionado correctamente']);
    }

    /**
     * @Route("/user/edit_user", name = "user_edit_user")
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction(Request $request){
        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $userRepository = $em->getRepository('UserBundle:User');

            $id = $request->request->get('id');
            $user = $userRepository->findById($id)[0];

            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user,$request->request->get('plainPassword1'));
            $user->setPassword($encoded);
            //$user->setEnabled(true);
            $roles=$request->request->get('role');

            ($roles != '') ? ($user->setRoles($roles)) : ($user->setRoles(array()));

            //if($form->isValid()){
            $userRepository->save($user);
            return new JsonResponse(['success' => true, 'sms' => 'Editado correctamente']);
            //}

            //return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/user/{id}/delete_user", name = "user_delete_user")
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request,$id){
        $em = $this->get('doctrine')->getEntityManager();
        $user = $em->getRepository('UserBundle:User')->find($id);
        $em->remove($user);
        $em->flush();
        return new JsonResponse(['success' => true, 'sms' => 'El usuario ha sido eliminado satisfactoriamente']);
    }

    /**
     * @Route("/user/{id}/change_status", name="user_change_status")
     * @param Request $request
     * @ParamConverter("user", class="UserBundle\Entity\User")
     * @param User $user
     * @return JsonResponse
     */
    public function changeStatusAction(Request $request, User $user){
        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $userRepository = $em->getRepository('UserBundle:User');
            $enabled = $request->request->get('enabled');

            $enabled = ($enabled == 'true') ? (true) : (false);
            $user->setEnabled($enabled);
            $userRepository->save($user);
            if($enabled){
               return new JsonResponse(['success' => true, 'sms' => 'Usuario habilitado']);
            }
            return new JsonResponse(['success' => true, 'sms' => 'Usuario deshabilitado']);
        }
    }
}
