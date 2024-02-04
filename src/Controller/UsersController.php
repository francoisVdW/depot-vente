<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{

    /**
     * initialize()
     *
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow(['logout', 'login']);
    }


    /**
     * isAuthorized
     *
     * @param $user
     *
     * @return bool
     */
    public function isAuthorized($user)
    {
        $action = $this->request->getParam('action');
        switch($action) {
            case 'login':
            case 'logout':
                return true;
            default:
                return $this->authorizations['ADMIN'];
        }
    }


    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = ['limit' => 10];
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }


    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        $this->set(compact('user'));
    }


    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function ajaxAdd()
    {
        $msg = '';
        $status = 0;
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $role_err = $this->setRoles($data); // ctrl erreur role et json si OK
            $user = $this->Users->patchEntity($user, $data);
            if ($role_err) $user->setError('a_role', $role_err);
            if (!$user->getErrors()) {
                $user->set('password', md5($data['password']));
                if ($user = $this->Users->save($user)) {
                    $status = 1; // success
                    $msg = 'L\'utilisateur ' . $user->title . ' a été créé';
                } else {
                    $status = -1; // Erreur
                    $msg = 'Echec de création de l`utilisateur';
                }
            } else {
                $status = -1; // Erreur
                $msg = 'Echec de création de l`utilisateur'.($role_err? ' - '.$role_err:'');
            }
        }
        $user->set('password', '');
        $this->set('status', $status);
        $this->set('msg', $msg);
        $this->set('user', $user);
    }


    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function ajaxEdit($id = null)
    {
        $current_user = $this->Auth->user();
        if ($current_user['id'] == $id) {
            $this->set('status', -2); // fatal
            $this->set('msg', 'Vous me pouvez pas modifier votre propre profil !');
            return;
        }
        $msg = '';
        $status = 0;
        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (isset($data['password']) && empty($data['password'])) {
                $this->set('status', -1); // fatal
                $this->set('msg', 'Vous ne pouvez pas laisser un mot de passe vide');
                $this->set('user', $user);
                return;
            }
            $role_err = $this->setRoles($data); // ctrl erreur role et json si OK
            $user = $this->Users->patchEntity($user, $data);
            if ($role_err) $user->setError('a_role', $role_err);
            if (!$user->getErrors()) {
                if (!empty($data['password'])) $user->set('password', md5($data['password']));
                if ($this->Users->save($user)) {
                    $status = 1; // success
                    $msg = 'L\'utilisateur ' . $user->title . ' a été mis à jour';
                } else {
                    $status = -1; // Erreur
                    $msg = 'Echec de mise à jour de l`utilisateur';
                }
            } else {
                $status = -1; // Erreur
                $msg = 'Echec de création de l`utilisateur'.($role_err? ' - '.$role_err:'');
            }
        } else {
            $user->set('a_role', $user->aRoles);
        }
        $this->set('status', $status);
        $this->set('msg', $msg);
        $this->set('user', $user);
    }


    /**
     * ajaxDelete()
     *
     * @param null $id
     */
    public function ajaxDelete($id = null)
    {
        $user = $this->Users->get($id);
        $current_user = $this->Auth->user();
        if ($current_user['id'] == $user->id) {
            $this->set('status', -2);
            $this->set('msg', 'Vous ne pouvez pas supprimer votre propre compte !');
            return;
        }
        $msg = '';
        $status = 0;
        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->Users->delete($user)) {
                $status = 1;
                $msg = 'Utilisateur ' . $user->title . ' supprimé';
            } else {
                $status = -1;
                $msg = 'Echec lors de la suppression de l\'utilisateur';
            }
        }
        $this->set('user', $user);
        $this->set('status', $status);
        $this->set('msg', $msg);
    }


    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }


    /**
     * login()
     *
     * @return \Cake\Http\Response|null
     */
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error('Nom de connexion / mot de passe incorrect');
        }
    }


    /**
     * logout()
     *
     * @return \Cake\Http\Response|null
     */
    public function logout()
    {
        $this->Flash->success('vous êtes déconnecté');
        return $this->redirect($this->Auth->logout());
    }


    /**
     * setRoles()
     * Ctrl : présence sélection Role, Sélection valide
     * si ctrl OK : valorise data['roles_json']
     * si Non :  retourne string : msg d'erreur
     *
     * @param array $data
     *
     * @return false|string
     */
    private function setRoles(array &$data)
    {
        if (!empty($data['a_role'])) {
            $role_err = false;
            $role_defs = \Cake\Core\Configure::read('roles');
            foreach ($data['a_role'] as $r) {
                if (!array_key_exists($r, $role_defs)) {
                    // Erreur
                    $role_err = 'Sélection invalide';
                    break;
                }
            }
            sort($data['a_role']);
            $data['roles_json'] = json_encode($data['a_role']);
        } else {
            // Erreur un rôle doit être defini !
            $role_err = 'Selectionnez au moins un rôle';
        }
        return $role_err;
    }
}
