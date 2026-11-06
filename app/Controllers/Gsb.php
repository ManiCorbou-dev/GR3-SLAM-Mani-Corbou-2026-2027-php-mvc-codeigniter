<?php

namespace App\Controllers;

use App\Models\GsbModel;
use CodeIgniter\Controller;
use DateTimeImmutable;

class Gsb extends Controller
{
    protected GsbModel $model;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->model = new GsbModel();
    }

    public function index()
    {
        
        return $this->acceuille();
    }
    public function acceuille()
    {
        return view('gsb_home');
    }

    // Ancien nom (faute de frappe) conservé pour ne casser aucune route existante
    public function conctact()
    {
        return $this->contact();
    }

    // gsb_dashboard a besoin de $user et $fiche : on passe toujours par expense()
    public function dashboard()
    {
        return redirect()->to(site_url('expense'));
    }

    public function history()
    {
        return view('gsb_history');
    }

    public function search()
    {
        return view('gsb_search');
    }

    public function about()
    {
        return view('gsb_about');
    }

    public function contact()
    {
        return view('gsb_contact');
    }

    public function legal()
    {
        return view('gsb_legal');
    }

    public function login()
    {
        $user = session()->get('user');

        if (is_array($user)) {
            return redirect()->to(($user['role'] ?? null) === 'visiteur' ? site_url('expense') : site_url('/'));
        }

        return view('gsb_login', [
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function authenticate()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $user = $this->model->authenticate(
            is_string($username) ? $username : '',
            is_string($password) ? $password : ''
        );

        if ($user === null) {
            session()->setFlashdata('error', 'Nom d’utilisateur ou mot de passe incorrect.');

            return redirect()->to(site_url('login'));
        }

        if ($user['role'] !== 'visiteur') {
            session()->setFlashdata(
                'error',
                'Cet espace est réservé aux visiteurs médicaux dans la version actuellement migrée.'
            );

            return redirect()->to(site_url('login'));
        }

        session()->regenerate(true);
        session()->set('user', $user);

        return redirect()->to(site_url('expense'));
    }

    public function expense()
    {
        $user = session()->get('user');

        if (! is_array($user) || ($user['role'] ?? null) !== 'visiteur') {
            if (is_array($user)) {
                session()->setFlashdata('error', 'Cette fiche est réservée aux visiteurs médicaux.');

                return redirect()->to(site_url('/'));
            }

            return redirect()->to(site_url('login'));
        }

        $requestedMonth = $this->request->getGet('month');

        if ($requestedMonth !== null && (! is_string($requestedMonth) || ! $this->isValidMonth($requestedMonth))) {
            return redirect()->to(site_url('expense'))->with('error', 'Le mois sélectionné est invalide.');
        }

        $month = $requestedMonth ?? date('Y-m');

        return view('gsb_dashboard', [
            'user'  => $user,
            'month' => $month,
            'fiche' => $this->model->getFiche($user['id'], str_replace('-', '', $month)),
        ]);
    }

    public function saveForfaits()
    {
        $user = $this->requireVisitor();

        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $month = $this->postedMonth();

        if ($month === null) {
            session()->setFlashdata('error', 'Le mois sélectionné est invalide.');

            return redirect()->to(site_url('expense'));
        }

        $fields = [
            'forfait_etape',
            'forfait_km',
            'forfait_nuitees',
            'forfait_repas',
        ];
        $quantities = [];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $quantity = ($value === null || $value === '')
                ? 0
                : (is_string($value) ? filter_var($value, FILTER_VALIDATE_INT) : false);

            if ($quantity === false || $quantity < 0) {
                session()->setFlashdata('error', 'Les quantités doivent être des nombres entiers positifs ou nuls.');

                return redirect()->to($this->expenseUrl($month));
            }

            $quantities[$field] = $quantity;
        }

        $this->model->saveForfaits($user['id'], str_replace('-', '', $month), $quantities);
        session()->setFlashdata('success', 'Forfaits enregistrés.');

        return redirect()->to($this->expenseUrl($month));
    }

    public function addHorsForfait()
    {
        $user = $this->requireVisitor();

        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $month = $this->postedMonth();
        $date = $this->request->getPost('hfDate');
        $label = $this->request->getPost('hfLabel');
        $amount = $this->request->getPost('hfAmount');
        $dateObject = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        $dateErrors = DateTimeImmutable::getLastErrors();
        $validDate = $dateObject !== false
            && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
            && $dateObject->format('Y-m-d') === $date;

        if (
            $month === null
            || ! $validDate
            || $dateObject->format('Y-m') !== $month
            || ! is_string($label)
            || trim($label) === ''
            || mb_strlen(trim($label)) > 100
            || ! is_string($amount)
            || ! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)
            || (float) $amount <= 0
            || (float) $amount > 99999999.99
        ) {
            session()->setFlashdata('error', 'Saisissez un libellé, une date du mois sélectionné et un montant valide.');

            return redirect()->to($month === null ? site_url('expense') : $this->expenseUrl($month));
        }

        $this->model->addHorsForfait(
            $user['id'],
            str_replace('-', '', $month),
            $date,
            trim($label),
            (float) $amount
        );
        session()->setFlashdata('success', 'Frais hors forfait ajouté.');

        return redirect()->to($this->expenseUrl($month));
    }

    public function deleteHorsForfait()
    {
        $user = $this->requireVisitor();

        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $month = $this->postedMonth();
        $rawId = $this->request->getPost('id');
        $expenseId = is_string($rawId) ? filter_var($rawId, FILTER_VALIDATE_INT) : false;

        if ($month === null || $expenseId === false || $expenseId < 1) {
            session()->setFlashdata('error', 'La suppression de ce frais est impossible.');

            return redirect()->to($month === null ? site_url('expense') : $this->expenseUrl($month));
        }

        $deleted = $this->model->deleteHorsForfait(
            $user['id'],
            str_replace('-', '', $month),
            $expenseId
        );
        session()->setFlashdata(
            $deleted ? 'success' : 'error',
            $deleted ? 'Frais supprimé.' : 'Ce frais n’existe pas dans cette fiche.'
        );

        return redirect()->to($this->expenseUrl($month));
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('/'));
    }

    private function requireVisitor()
    {
        $user = session()->get('user');

        if (! is_array($user)) {
            return redirect()->to(site_url('login'));
        }

        if (($user['role'] ?? null) !== 'visiteur') {
            session()->setFlashdata('error', 'Cette action est réservée aux visiteurs médicaux.');

            return redirect()->to(site_url('/'));
        }

        return $user;
    }

    private function postedMonth(): ?string
    {
        $month = $this->request->getPost('month');

        return is_string($month) && $this->isValidMonth($month) ? $month : null;
    }

    private function isValidMonth(string $month): bool
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1;
    }

    private function expenseUrl(string $month): string
    {
        return site_url('expense') . '?month=' . rawurlencode($month);
    }
}
