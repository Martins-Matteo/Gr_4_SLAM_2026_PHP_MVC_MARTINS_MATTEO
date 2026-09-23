<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * GSB - Gestion des frais (MVC CodeIgniter)
 * Toutes les demandes passent par la fonction index() du Controleur,
 * qui joue le rôle de contrôleur frontal.
 */

// Point d'entrée du site
$routes->get('/', 'Controleur::index');

// Transmission des données envoyées par un formulaire (méthode POST)
$routes->post('postdata', 'Controleur::index');

// Transmission des données envoyées par un lien (méthode GET)
$routes->get('getdata', 'Controleur::index');
