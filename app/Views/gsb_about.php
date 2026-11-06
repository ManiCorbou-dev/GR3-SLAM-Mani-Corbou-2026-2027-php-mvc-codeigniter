<?php echo view('partials/gsb_header', ['title' => 'À propos']); ?>
<main class="main">
    <div class="logo">
        <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" width="400">
    </div>
    <h1>À propos</h1>
    <p>
        GSB est une organisation de référence dans le domaine pharmaceutique, animée par des valeurs fortes de
        responsabilité, d'éthique et de développement durable. Nous concevons, produisons et commercialisons des
        solutions thérapeutiques pour répondre aux enjeux de santé publique.
    </p>
    <h2>Nos valeurs</h2>
    <ul>
        <li>Innovation responsable.</li>
        <li>Exigence qualité et sécurité.</li>
        <li>Engagement patient et éthique.</li>
        <li>Travail d'équipe et transmission de savoir-faire.</li>
    </ul>
</main>
<?php echo view('partials/gsb_footer'); ?>
