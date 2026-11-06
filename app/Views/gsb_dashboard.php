<?php
$month = $month ?? date('Y-m');
$fiche = $fiche ?? ['forfaits' => ['forfait_etape' => 0, 'forfait_km' => 0, 'forfait_nuitees' => 0, 'forfait_repas' => 0], 'horsForfait' => []];
$user = $user ?? session()->get('user');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note de Frais GSB</title>
    <link rel="stylesheet" href="<?= base_url('/css/gsb.css') ?>">
    <link rel="icon" href="<?= base_url('images/logo-gsb.png') ?>">
</head>
<body>
    <div class="header-connected">
        <div style="text-align: center; flex: 1;">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" style="height: 40px;">
        </div>
        <div class="user-info">
            <div class="profile-badge">
                <div class="profile-icon">👤</div>
                <div class="profile-text">
                    <h3><?= esc($user['firstName'] . ' ' . $user['lastName']) ?></h3>
                    <p><?= esc($user['profile']) ?></p>
                </div>
            </div>
            <a class="logout-btn" href="<?= site_url('logout') ?>">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <h1 style="color: #8ab98b; margin-bottom: 30px;">Espace de Gestion des Notes de Frais</h1>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="success-message" style="padding: 12px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 16px;">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="error-message" style="padding: 12px; background: #f8d7da; color: #721c24; border-radius: 6px; margin-bottom:16px;">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-button active" type="button" data-tab="new-expense">Nouvelle note</button>
            <button class="tab-button" type="button" data-tab="history">Historique</button>
            <button class="tab-button" type="button" data-tab="info">Informations</button>
        </div>

        <div id="new-expense" class="tab-content active">
            <h2>Renseigner fiche de frais</h2>

            <form method="get" action="<?= site_url('expense') ?>" style="display:flex; gap:12px; align-items:center; margin-top:0; margin-bottom: 20px;">
                <label for="month" style="font-weight:600;">Mois :</label>
                <input type="month" id="month" name="month" value="<?= esc($month) ?>">
                <button type="submit" class="submit-btn">Charger</button>
            </form>

            <section style="margin-bottom:20px;">
                <h3 style="margin:0 0 10px 0; color:#7b90a6;">Éléments forfaitisés</h3>
                <?= form_open('/expense/save-forfaits', ['style' => 'margin:0;']) ?>
                    <input type="hidden" name="month" value="<?= esc($month) ?>">
                    <div class="expense-form">
                        <div class="form-group">
                            <label for="forfait_etape">Forfait Étape (quantité)</label>
                            <input type="number" id="forfait_etape" name="forfait_etape" min="0" value="<?= esc($fiche['forfaits']['forfait_etape']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="forfait_km">Frais kilométriques (km)</label>
                            <input type="number" id="forfait_km" name="forfait_km" min="0" value="<?= esc($fiche['forfaits']['forfait_km']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="forfait_nuitees">Nuitées hôtel (nb)</label>
                            <input type="number" id="forfait_nuitees" name="forfait_nuitees" min="0" value="<?= esc($fiche['forfaits']['forfait_nuitees']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="forfait_repas">Repas restaurant (nb)</label>
                            <input type="number" id="forfait_repas" name="forfait_repas" min="0" value="<?= esc($fiche['forfaits']['forfait_repas']) ?>">
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <button type="submit" class="submit-btn">Enregistrer forfaits</button>
                    </div>
                <?= form_close() ?>
            </section>

            <section>
                <h3 style="margin:0 0 10px 0; color:#7b90a6;">Frais hors forfait</h3>

                <table class="expenses-table" id="hfTable" style="margin-bottom:8px;">
                    <thead>
                        <tr><th>Date</th><th>Libellé</th><th>Montant</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fiche['horsForfait'])): ?>
                            <?php foreach ($fiche['horsForfait'] as $hf): ?>
                                <tr>
                                    <td><?= esc($hf['date']) ?></td>
                                    <td><?= esc($hf['libelle']) ?></td>
                                    <td><?= number_format((float) $hf['montant'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <?= form_open('/expense/delete-hors-forfait', ['style' => 'margin:0;']) ?>
                                            <input type="hidden" name="month" value="<?= esc($month) ?>">
                                            <input type="hidden" name="id" value="<?= esc($hf['id']) ?>">
                                            <button type="submit" class="submit-btn">Supprimer</button>
                                        <?= form_close() ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Aucun frais hors forfait enregistré.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?= form_open('/expense/add-hors-forfait', ['id' => 'hfForm', 'style' => 'margin:0;']) ?>
                    <input type="hidden" name="month" value="<?= esc($month) ?>">
                    <div class="expense-form">
                        <div class="form-group">
                            <label for="hfDate">Date d'engagement</label>
                            <input type="date" id="hfDate" name="hfDate" min="<?= esc($month) ?>-01" max="<?= esc(date('Y-m-t', strtotime($month . '-01'))) ?>" value="<?= esc($month) ?>-01" required>
                        </div>
                        <div class="form-group">
                            <label for="hfLabel">Libellé</label>
                            <input type="text" id="hfLabel" name="hfLabel" required>
                        </div>
                        <div class="form-group">
                            <label for="hfAmount">Montant (€)</label>
                            <input type="number" id="hfAmount" name="hfAmount" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="submit-btn">Ajouter hors forfait</button>
                        </div>
                    </div>
                <?= form_close() ?>
            </section>
        </div>

        <div id="history" class="tab-content">
            <h2>Consulter fiche de frais</h2>
            <div style="margin-bottom:16px; display:flex; gap:12px; align-items:center;">
                <label for="consultMonth" style="font-weight:600;">Sélectionnez un mois :</label>
                <input type="month" id="consultMonth" value="<?= esc($month) ?>">
                <button type="button" class="submit-btn" onclick="location.href='<?= site_url('expense') ?>?month='+document.getElementById('consultMonth').value">Afficher</button>
            </div>

            <h3>Fiche de frais - <?= esc($month) ?></h3>
            <h4>Forfaits</h4>
            <ul>
                <li>Forfait Étape: <?= esc($fiche['forfaits']['forfait_etape']) ?></li>
                <li>Frais kilométriques: <?= esc($fiche['forfaits']['forfait_km']) ?> km</li>
                <li>Nuitées hôtel: <?= esc($fiche['forfaits']['forfait_nuitees']) ?></li>
                <li>Repas restaurant: <?= esc($fiche['forfaits']['forfait_repas']) ?></li>
            </ul>
            <h4>Hors forfait</h4>
            <?php if (empty($fiche['horsForfait'])): ?>
                <p>Aucun frais hors forfait.</p>
            <?php else: ?>
                <table class="expenses-table">
                    <thead>
                        <tr><th>Date</th><th>Libellé</th><th>Montant</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fiche['horsForfait'] as $hf): ?>
                            <tr>
                                <td><?= esc($hf['date']) ?></td>
                                <td><?= esc($hf['libelle']) ?></td>
                                <td><?= number_format((float) $hf['montant'], 2, ',', ' ') ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="info" class="tab-content">
            <h2>Informations sur les visiteurs médicaux</h2>
            <p style="line-height: 1.8; color: #666;">
                La force commerciale d'un laboratoire pharmaceutique est assurée par un travail de conseil et
                d'information auprès des prescripteurs. Les visiteurs médicaux (ou délégués) démarchent les
                médecins, pharmaciens, infirmières et autres métiers de santé susceptibles de prescrire aux
                patients les produits du laboratoire.
            </p>

            <h3 style="color: #7b90a6; margin-top: 25px;">Gestion des frais</h3>
            <p style="line-height:1.8; color:#666;">
                Une gestion unique de ces frais et remboursement pour l'ensemble de la flotte visite est souhaitée.
                Les visiteurs peuvent soumettre leurs notes de frais via cette plateforme pour un traitement rapide
                et transparent.
            </p>
        </div>
    </div>

    <script src="<?= base_url('/js/gsb.js') ?>"></script>
</body>
</html>
