<?php echo view('partials/gsb_header', ['title' => 'Contact']); ?>
<main class="main">
    <div class="logo">
        <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" width="400">
    </div>
    <h1>Contactez-nous</h1>
    <p>
        Nous aimerions entendre parler de vous. Que vous ayez des questions, des suggestions ou que vous
        souhaitiez explorer des opportunités de partenariat, n'hésitez pas à nous contacter.
    </p>
    <h2>Formulaire de contact</h2>
    <form>
        <label for="name">Nom complet:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Adresse e-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Téléphone (optionnel):</label>
        <input type="tel" id="phone" name="phone">

        <label for="subject">Sujet:</label>
        <select id="subject" name="subject" required>
            <option value="">-- Sélectionnez un sujet --</option>
            <option value="general">Question générale</option>
            <option value="partnership">Opportunité de partenariat</option>
            <option value="research">Recherche et innovation</option>
            <option value="employment">Offre d'emploi</option>
            <option value="press">Demande médias</option>
            <option value="other">Autre</option>
        </select>

        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="6" required></textarea>

        <button type="submit">Envoyer</button>
    </form>

    <h2>Coordonnées directes</h2>
    <p>
        <strong>Siège social :</strong><br>
        GSB (Groupe Spectra Biopharma)<br>
        123 Avenue de la République<br>
        75010 Paris, France
    </p>
    <p>
        <a href="tel:+03XXXXXXXX">tel : +33 XXXXXXXXXX</a><br>
        <a href="mailto:contact@gsb.fr">contact@gsb.fr</a>
    </p>
</main>
<?php echo view('partials/gsb_footer'); ?>
