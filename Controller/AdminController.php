<?php

namespace Aqpglug\CodemedoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Aqpglug\CodemedoBundle\Controller\BaseController;
use Aqpglug\CodemedoBundle\Entity\Block;
use Aqpglug\CodemedoBundle\Form\BlockType;

/**
 * @Route("/block")
 */
class AdminController extends BaseController
{

    /**
     * @Route("/list/{type}/{page}", name="cmd_admin_block", defaults={"page"=1, "type"="page"})
     * @Secure(roles="ROLE_ADMIN")
     */
    public function listAction($type, $page)
    {
        $step = 13;
        $pages = $this->countPagesBy(array('type' => $type), $step);

        $blocks = $this->getRepo()->findBy(
                        array('type' => $type,), array('created' => 'DESC',), $step, $step * ($page - 1));

        // Algun bug en la session no borra la flash
        $this->getRequest()->getSession()->removeFlash('notice');
        if(!count($blocks))
            $this->getRequest()->getSession()->setFlash('notice', sprintf("no hay %ss", $this->getConfig()->getLabel($type)));

        return $this->render('AqpglugCodemedoBundle:Admin:list.html.twig', array(
            'blocks' => $blocks,
            'type' => $type,
            'page' => $page,
            'pages' => $pages,
        ));
    }

    /**
     * @Route("/edit/{id}", name="cmd_admin_block_edit")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editAction($id)
    {
        $block = $this->getRepo()->findOneBy(array('id' => $id));

        $meta = $this->get('codemedo')->getMeta($block->getType());
        $form = $this->createForm(new BlockType($meta), $block);

        $request = $this->getRequest();
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);

            if($form->isValid())
            {
                $block->autoslug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($block);
                $em->flush();
                return $this->redirect($this->generateUrl('cmd_admin_block', array('type' => $block->getType())));
            }
        }

        return $this->render('AqpglugCodemedoBundle:Admin:form.html.twig', array(
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmd_admin_block_edit', array('id' => $id)),
            'type' => $block->getType(),
        ));
    }

    /**
     * @Route("/{type}/new", name="cmd_admin_block_new")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction($type)
    {
        $block = new Block();
        $block->setPublished(true);

        $meta = $this->get('codemedo')->getMeta($type);
        $form = $this->createForm(new BlockType($meta), $block);

        $request = $this->getRequest();
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);

            if($form->isValid())
            {
                //print_r($form['image']); die();
                //$block->setImage($this->saveImage($form['image'], md5(time()), $type));
                $block->autoslug();
                $block->setType($type);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($block);
                $em->flush();
                return $this->redirect($this->generateUrl(
                                'cmd_admin_block', array('type' => $block->getType())));
            }
        }

        return $this->render('AqpglugCodemedoBundle:Admin:form.html.twig', array(
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmd_admin_block_new', array('type' => $type)),
            'type' => $type,
        ));
    }

    /**
     * @Route("/remove/{id}", name="cmd_admin_block_remove", requirements={"_method"="POST"})
     * @Secure(roles="ROLE_ADMIN")
     */
    public function removeAction($id)
    {
        $block = $this->getRepo()->findOneBy(array('id' => $id));
        $type = $block->getType();
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($block);
        $em->flush();
        return $this->redirect($this->generateUrl('cmd_admin_block', array('type' => $type)));
    }

    /**
     * @Route("/publish/{id}", name="cmd_admin_block_publish")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function publishAction($id)
    {
        $block = $this->getRepo()->findOneBy(array('id' => $id));
        $block->setPublished(!$block->getPublished());

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($block);
        $em->flush();
        return $this->redirect($this->generateUrl('cmd_admin_block', array('type' => $block->getType())));
    }

    /**
     * @Route("/feature/{id}", name="cmd_admin_block_feature")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function featureAction($id)
    {
        $block = $this->getRepo()->findOneBy(array('id' => $id));
        $block->setFeatured(!$block->getFeatured());

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($block);
        $em->flush();
        return $this->redirect($this->generateUrl('cmd_admin_block', array('type' => $block->getType())));
    }

    private function saveImage(UploadedFile $file, $name, $type)
    {
        $extension = $file->guessExtension();
        if (!$extension) {
            // extension cannot be guessed
            $extension = 'bin';
        }
        $webroot = $this->getRequest()->server['DOCUMENT_ROOT'];
        $dir = 'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR;
        $file->move($webroot.DIRECTORY_SEPARATOR.$dir, $name.'.'.$extension);
        return $dir.DIRECTORY_SEPARATOR.$name.'.'.$extension;
    }
}
