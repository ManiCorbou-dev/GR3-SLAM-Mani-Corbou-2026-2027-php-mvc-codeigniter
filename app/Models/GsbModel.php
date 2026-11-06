<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;
use Throwable;

class GsbModel extends Model
{
    private const ROLE_TABLES = [
        'visiteur'       => 'visiteur',
        'comptable'      => 'comptable',
        'administrateur' => 'administrateur',
    ];

    private const FORFAIT_FIELDS = [
        'ETP' => 'forfait_etape',
        'KM'  => 'forfait_km',
        'NUI' => 'forfait_nuitees',
        'REP' => 'forfait_repas',
    ];

    public function authenticate(string $login, string $password): ?array
    {
        $login = trim($login);

        if ($login === '' || $password === '') {
            return null;
        }

        foreach (self::ROLE_TABLES as $role => $table) {
            $user = $this->db->table($table)
                ->select('id, nom, prenom, login, mdp')
                ->where('login', $login)
                ->get()
                ->getRowArray();

            if ($user === null || ! hash_equals((string) $user['mdp'], $password)) {
                continue;
            }

            return [
                'id'        => $user['id'],
                'username'  => $user['login'],
                'login'     => $user['login'],
                'role'      => $role,
                'profile'   => $role === 'visiteur' ? 'Visiteur médical' : ucfirst($role),
                'firstName' => $user['prenom'],
                'lastName'  => $user['nom'],
            ];
        }

        return null;
    }

    public function getFiche(string $visitorId, string $month): array
    {
        $fiche = $this->getDefaultFiche();

        $forfaits = $this->db->table('LigneFraisForfait')
            ->select('idFraisForfait, quantite')
            ->where('idVisiteur', $visitorId)
            ->where('mois', $month)
            ->get()
            ->getResultArray();

        foreach ($forfaits as $forfait) {
            $field = self::FORFAIT_FIELDS[$forfait['idFraisForfait']] ?? null;

            if ($field !== null) {
                $fiche['forfaits'][$field] = (int) $forfait['quantite'];
            }
        }

        $fiche['horsForfait'] = $this->db->table('LigneFraisHorsForfait')
            ->select('id, date, libelle, montant')
            ->where('idVisiteur', $visitorId)
            ->where('mois', $month)
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->getResultArray();

        return $fiche;
    }

    public function saveForfaits(string $visitorId, string $month, array $quantities): void
    {
        $this->db->transBegin();

        try {
            $this->ensureFicheExists($visitorId, $month);

            foreach (self::FORFAIT_FIELDS as $forfaitId => $field) {
                $this->db->query(
                    'INSERT INTO LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)',
                    [$visitorId, $month, $forfaitId, $quantities[$field]]
                );
            }

            $this->updateFicheSummary($visitorId, $month);
            $this->commitTransaction();
        } catch (Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }
    }

    public function addHorsForfait(
        string $visitorId,
        string $month,
        string $date,
        string $label,
        float $amount
    ): void {
        $this->db->transBegin();

        try {
            $this->ensureFicheExists($visitorId, $month);
            $this->db->table('LigneFraisHorsForfait')->insert([
                'idVisiteur' => $visitorId,
                'mois'       => $month,
                'libelle'    => $label,
                'date'       => $date,
                'montant'    => $amount,
            ]);

            $this->updateFicheSummary($visitorId, $month);
            $this->commitTransaction();
        } catch (Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }
    }

    public function deleteHorsForfait(string $visitorId, string $month, int $expenseId): bool
    {
        $this->db->transBegin();

        try {
            $this->db->table('LigneFraisHorsForfait')
                ->where('id', $expenseId)
                ->where('idVisiteur', $visitorId)
                ->where('mois', $month)
                ->delete();

            $deleted = $this->db->affectedRows() > 0;

            if ($deleted) {
                $this->updateFicheSummary($visitorId, $month);
            }

            $this->commitTransaction();

            return $deleted;
        } catch (Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }
    }

    public function getDefaultFiche(): array
    {
        return [
            'forfaits' => [
                'forfait_etape'   => 0,
                'forfait_km'      => 0,
                'forfait_nuitees' => 0,
                'forfait_repas'   => 0,
            ],
            'horsForfait' => [],
        ];
    }

    private function ensureFicheExists(string $visitorId, string $month): void
    {
        $exists = $this->db->table('FicheFrais')
            ->where('idVisiteur', $visitorId)
            ->where('mois', $month)
            ->countAllResults() > 0;

        if (! $exists) {
            $this->db->table('FicheFrais')->insert([
                'idVisiteur'    => $visitorId,
                'mois'          => $month,
                'nbJustificatif' => 0,
                'montantValide'  => 0,
                'dateModif'      => date('Y-m-d'),
                'idEtat'         => 'CR',
            ]);
        }
    }

    private function updateFicheSummary(string $visitorId, string $month): void
    {
        $forfaits = $this->db->query(
            'SELECT COALESCE(SUM(l.quantite * f.montant), 0) AS total
             FROM LigneFraisForfait AS l
             JOIN FraisForfait AS f ON f.id = l.idFraisForfait
             WHERE l.idVisiteur = ? AND l.mois = ?',
            [$visitorId, $month]
        )->getRowArray();

        $horsForfait = $this->db->query(
            'SELECT COALESCE(SUM(montant), 0) AS total
             FROM LigneFraisHorsForfait
             WHERE idVisiteur = ? AND mois = ?',
            [$visitorId, $month]
        )->getRowArray();

        $this->db->table('FicheFrais')
            ->where('idVisiteur', $visitorId)
            ->where('mois', $month)
            ->update([
                'dateModif'     => date('Y-m-d'),
                'montantValide' => (float) $forfaits['total'] + (float) $horsForfait['total'],
            ]);
    }

    private function commitTransaction(): void
    {
        if (! $this->db->transStatus()) {
            throw new RuntimeException('La transaction de la fiche de frais a échoué.');
        }

        $this->db->transCommit();
    }
}
