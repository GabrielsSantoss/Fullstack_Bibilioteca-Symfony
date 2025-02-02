<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController {

    #[ Route( '/', name: 'home' ) ]

    public function redirectToBooks() {
        return $this->redirectToRoute( 'book_list' );
    }

    #[ Route( '/books', name: 'book_list', methods: [ 'GET' ] ) ]

    public function bookList(): Response {
        return $this->render( 'bookcontroller/index.html.twig' );
    }
}

?>