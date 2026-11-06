<?php echo view('partials/gsb_header', ['title' => 'Connexion GSB']); ?>
<main class="main">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Connexion GSB</h1>
                <p>Accédez à votre espace personnel</p>
            </div>

            <?php if ($error ?? null): ?>
                <div class="error-message" style="display:block; margin-bottom: 15px; color: #b00020; font-weight: 600;">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <?= form_open('/login', ['class' => 'login-form']) ?>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" placeholder="ex: lvillachane" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="ex: jux7g" required>
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="login-btn">Se connecter</button>
            <?= form_close() ?>

            <div class="login-footer">
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                <p style="font-size: 12px; color: var(--gray); margin-top: 15px;">
                    Compte de test : <br>
                    Login: <strong>lvillachane</strong><br>
                    MDP: <strong>jux7g</strong>
                </p>
                <p>
                    Pour créer un compte, veuillez contacter l'administrateur du système. <br>
                    Pour des raisons de sécurité, l'inscription en ligne n'est pas disponible.
                </p>
            </div>
        </div>
    </div>
</main>
<?php echo view('partials/gsb_footer'); ?>
