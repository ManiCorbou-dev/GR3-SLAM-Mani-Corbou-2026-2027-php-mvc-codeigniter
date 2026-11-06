<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Contexte GSB') ?></title>
    <link rel="stylesheet" href="<?= base_url('/css/gsb.css') ?>">
    <link rel="icon" href="<?= base_url('images/logo-gsb.png') ?>">
</head>
<body>
    <div id="wrap">
        <header class="header">
            <nav class="nav">
                <a href="#wrap" id="open">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px" y="0px" width="34px" height="27px" viewBox="0 0 34 27" enable-background="new 0 0 34 27"
                        xml:space="preserve">
                        <rect fill="#FFFFFF" width="34" height="4" />
                        <rect y="11" fill="#FFFFFF" width="34" height="4" />
                        <rect y="23" fill="#FFFFFF" width="34" height="4" />
                    </svg>
                </a>
                <a href="#" id="close">×</a>
                <h1><a href="<?= site_url('/') ?>">Contexte GSB</a></h1>
                <a href="<?= site_url('history') ?>">Notre Histoire</a>
                <a href="<?= site_url('search') ?>">Recherche et Innovation</a>
                <a href="<?= site_url('about') ?>">À propos</a>
                <?php if (session()->get('user')): ?>
                    <a href="<?= site_url('expense') ?>">Note de Frais</a>
                    <a href="<?= site_url('logout') ?>">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= site_url('login') ?>">Connexion</a>
                <?php endif; ?>
                <a href="<?= site_url('contact') ?>">Contact</a>
                <a href="<?= site_url('legal') ?>">Mentions légales</a>
            </nav>
        </header>
