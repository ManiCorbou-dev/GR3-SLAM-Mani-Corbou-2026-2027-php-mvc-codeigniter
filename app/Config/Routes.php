<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Pages publiques
$routes->get('/',        'Gsb::index');
$routes->get('history',  'Gsb::history');
$routes->get('search',   'Gsb::search');
$routes->get('about',    'Gsb::about');
$routes->get('contact',  'Gsb::contact');
$routes->get('legal',    'Gsb::legal');

// Authentification
$routes->get('login',   'Gsb::login');
$routes->post('login',  'Gsb::authenticate');
$routes->get('logout',  'Gsb::logout');

// Espace visiteur : notes de frais
$routes->get('dashboard',                    'Gsb::dashboard');
$routes->get('expense',                      'Gsb::expense');
$routes->post('expense/save-forfaits',       'Gsb::saveForfaits');
$routes->post('expense/add-hors-forfait',    'Gsb::addHorsForfait');
$routes->post('expense/delete-hors-forfait', 'Gsb::deleteHorsForfait');
