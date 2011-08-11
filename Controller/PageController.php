<?php

namespace Aqpglug\CodemedoBundle\Controller;

use Aqpglug\CodemedoBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("")
 */
class PageController extends BaseController
{
    /**
     * @Route("/{slug}", name="cmd_block_page_show", defaults={"slug"="homepage"})
     */
    public function showAction($slug)
    {
        $page = $this->getRepo()->findOnePublishedBy(array(
                    'type' => 'page',
                    'slug' => $slug));
        
        if (is_null($page)) {
            throw $this->createNotFoundException("Página no encontrada");
        }

        return $this->render('AqpglugCodemedoBundle:Page:show.html.twig', array(
            'page' => $page,
        ));
    }
}
