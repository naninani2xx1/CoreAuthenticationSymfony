<?php

declare(strict_types=1);

namespace App\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    public function logout(): Response
    {
        //remove token is here
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
