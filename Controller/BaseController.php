<?php

namespace Aqpglug\CodemedoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Aqpglug\CodemedoBundle\Repository\BlockRepository;
use Aqpglug\CodemedoBundle\Extension\Config;

/**
 * @Route("")
 */
class BaseController extends Controller
{

    /**
     * @return BlockRepository
     */
    public function getRepo()
    {
        return $this->getDoctrine()->getRepository('Aqpglug\CodemedoBundle\Entity\Block');
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->get('codemedo');
    }

    public function countPagesBy(array $criteria, $step = 10)
    {
        $count = $this->getRepo()->countBy($criteria);
        return ceil($count / $step);
    }
}
