<?php
declare(strict_types=1);

namespace App\Controller;

use \Cake\ORM\Query;
/**
 * CashRegisters Controller
 *
 * @property \App\Model\Table\CashRegistersTable $CashRegisters
 * @method \App\Model\Entity\CashRegister[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CashRegistersController extends AppController
{

    /**
     * isAuthorized()
     *
     * @param null $user
     *
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        $action = $this->request->getParam('action');
        switch($action) {
            case 'index':
            case 'open':
            case 'close':
            case 'view':
            case 'idle':
                return $this->authorizations['CashRegisters'];
            case 'adminIndex':
            case 'ajaxAdd':
            case 'ajaxDelete':
                return $this->authorizations['ADMIN'];
            case 'ajaxSetFund':
            case 'ajaxSetUser':
            case 'mngrComplete':
            case 'mngrDashboard':
            case 'mngrSetComplete':
            case 'mngrCashRegistersBalance':
                return $this->authorizations['MNGR'];
            default:
                $err_msg = __METHOD__."() action non reconnue [$action]";
                \Cake\Log\Log::error($err_msg);
                if (\Cake\Core\Configure::read('debug')) {
                	trigger_error($err_msg, E_USER_ERROR);
                }
                $this->Flash->info('Action non reconnue');
                return  false;
        }
    }


    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // $current_user = $this->Auth->user();
        $cashRegisters = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q) {
                return $q->select(['id', 'name', 'cash_register_id', 'state'])->where(['state IN' => ['NEW','SALE IN PROGRESS'] ]);
            })
            ->order(['name'])
            ->all();
        foreach ($cashRegisters as $cashRegister) {
            $this->redirectToSalesProceedIfNeeded($cashRegister);
        }
        $this->set('cashRegisters', $cashRegisters);
        $this->set('hh_info', $this->CashRegisters->Sales->happyHourInfo());
    }


    /**
     * adminIndex()
     * Gestion des caisse ADMIN
     *
     */
    public function adminIndex()
    {
        // $current_user = $this->Auth->user();
        $cashRegisters = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q) {
                return $q->select(['id', 'name', 'cash_register_id', 'state'])->where(['state IN' => ['NEW','SALE IN PROGRESS'] ]);
            })
            ->order(['name'])
            ->all();
        $this->set(compact('cashRegisters'));
    }


    /**
     * ajaxAdd()
     *
     */
    public function ajaxAdd()
    {
        $cashRegister = $this->CashRegisters->newEmptyEntity();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['state'] = 'CLOSED';
            $cashRegister = $this->CashRegisters->patchEntity($cashRegister, $data);
            if ($this->CashRegisters->save($cashRegister)) {
                $status = 1;
                $msg = 'La caisse '.$cashRegister->name.' a été créée';
            } else {
                $status = -1;
                $msg = 'Erreur de création de la caisse';
            }
        } else {
            $status = 0;
            $msg = '';
        }
        $this->set('status', $status);
        $this->set('msg', $msg);
        $this->set('cashRegister', $cashRegister);
    }

    /**
     * ajaxSetFund()
     *
     * @param int $id
     */
    public function ajaxSetFund($id)
    {
        $cashRegister = $this->CashRegisters->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $cashRegister = $this->CashRegisters->patchEntity($cashRegister, $this->request->getData());
            if ($this->CashRegisters->save($cashRegister, ['fields' => ['cash_fund']])) {
                $status = 1;
                $msg = 'Fond de caisse mis à jour';
            } else {
                $status = -1;
                $msg = 'Erreur de mise à jour du fond de caisse';
            }
        } else {
            $status = 0;
            $msg = '';
        }
        $this->set('status', $status);
        $this->set('msg', $msg);
        $this->set('cashRegister', $cashRegister);
    }


    /**
     * ajaxDelete()
     *
     * @param int $id
     */
    public function ajaxDelete($id)
    {
        $cashRegister = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q) {
                return $q->select(['id', 'state', 'cash_register_id'])->where(['state !=' => 'CANCELLED' ]);
            })
            ->where(['id' => $id])
            ->first();
        if (empty($cashRegister)) {
            $err_msg = __METHOD__."() caisse [$id] non trouvée";
            \Cake\Log\Log::error($err_msg);
            $this->set('status', -2);
            $this->set('msg', "Erreur caisse [$id] non trouvée");
            $this->set('cashRegister', false);
            return;
        }
        if ($cashRegister->has('sales')) {
            if (count($cashRegister->sales)) {
                $this->set('status', -2);
                $cnt_msg = (count($cashRegister->sales) > 1)? count($cashRegister->sales).' ventes': 'une vente';
                $this->set('msg', "Opération refusée : La caisse '{$cashRegister->name}' a déjà enregistré $cnt_msg");
                $this->set('cashRegister', $cashRegister);
                return;

            }
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->CashRegisters->delete($cashRegister)) {
                $status = 1;
                $msg = "Caisse '{$cashRegister->name}' supprimée";
            } else {
                $status = -1;
                $msg = "Echec lors de la suppression de la caisse '{$cashRegister->name}'";
            }
        } else {
            $status = 0;
            $msg = '';
        }
        $this->set('cashRegister', $cashRegister);
        $this->set('status', $status);
        $this->set('msg', $msg);
    }


    public function ajaxSetUser($id)
    {
        $cashRegister = $this->CashRegisters->get($id);
        // liste des utilisateur ayant les droits "C-REGISTER" (caisse) ET
        // qui ne sont pas liés à une caisse en activité
        $users =  $this->getTableLocator()->get('Users')->listAvailableCashFundUsers();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (!array_key_exists($data['user_id'], $users)) {
                $status = -1;
                $msg = 'L\'utilisateur sélectionné ne peut pas être assigné à cette caisse';
            } else {
                $data['ip'] = null;
                $data['user_title'] = $users[$data['user_id']];
                $cashRegister = $this->CashRegisters->patchEntity($cashRegister, $data);
                if ($cashRegister = $this->CashRegisters->save($cashRegister, ['fields' => ['user_id', 'user_title', 'ip']])) {
                    $status = 1;
                    $msg = 'L\'utilisateur assigné à cette caisse a été mis à jour';
                } else {
                    $status = -1;
                    $msg = 'Erreur de mise à jour de l\'utilisateur de cette caisse';
                }
            }
        } else {
            $status = 0;
            $msg = '';
        }
        $this->set('status', $status);
        $this->set('msg', $msg);
        $this->set('cashRegister', $cashRegister);
        $this->set('users', $users);
    }


    /**
     * mngrComplete()
     *
     */
    public function mngrComplete()
    {
        $cashRegisters = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q) {
                return $q->select(['id', 'name', 'cash_register_id', 'state'])->where(['state IN' => ['NEW','SALE IN PROGRESS'] ]);
            })
            ->order(['name'])
            ->all();
        foreach ($cashRegisters as $cashRegister) {
            $this->redirectToSalesProceedIfNeeded($cashRegister);
        }
        $this->set(compact('cashRegisters'));
    }


    /**
     * mngrDashboard()
     *
     */
    public function mngrDashboard()
    {
        $cashRegisters = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q) {
                return $q->select(['id', 'name', 'cash_register_id', 'state'])->where(['state IN' => ['NEW','SALE IN PROGRESS'] ]);
            })
            ->order(['name'])
            ->all();

        $this->loadModel('Items');
        $query = $this->Items->find();
        $items_counts = $query->select([
                'progress',
                'cnt' => $query->func()->count('progress')
                ])
            ->group(['progress'])
            ->enableHydration(false)
            ->all();
        $items_stats = [];
        // mis en forme cle / valeur
        foreach($items_counts as $items_count) {
            $items_stats[$items_count['progress']] = $items_count['cnt'];
        }


        $query = $this->Items->Deposits->find();
        $items_counts = $query->select([
            'progress',
            'cnt' => $query->func()->count('progress')
        ])
            ->group(['progress'])
            ->enableHydration(false)
            ->all();
        $deposits_stats = [];
        // mis en forme cle / valeur
        foreach($items_counts as $items_count) {
            $deposits_stats[$items_count['progress']] = $items_count['cnt'];
        }

        $hh_info = $this->CashRegisters->Sales->happyHourInfo();

        $this->set(compact('cashRegisters', 'items_stats', 'deposits_stats', 'hh_info'));
    }


    /**
     * idle()
     *
     * @param $id
     *
     * @return \Cake\Http\Response|null
     */
    public function idle($id)
    {
        $current_user = $this->Auth->user();
        $cashRegister = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q) {
                return $q->select(['id', 'name', 'cash_register_id', 'state'])
                    ->where(['state IN' => ['NEW','SALE IN PROGRESS'] ]);
            })
            ->where([
                'CashRegisters.id' => $id,
                'CashRegisters.user_id' => $current_user['id'],
                'CashRegisters.state IN' => ['OPEN', 'SALE IN PROGRESS'] ])
            ->first();
        if (empty($cashRegister)) {
            $this->Flash->info('La caisse demandée n\'est pas accessible');
            return $this->redirect(['action' => 'index']);
        }
        $this->redirectToSalesProceedIfNeeded($cashRegister);

        $this->set('hh_info', $this->CashRegisters->Sales->happyHourInfo());

        $this->set('cashRegister', $cashRegister);
    }



    /**
     * open()
     * Ouverture de la caisse et assign à l'utilisateur courant
     *
     * @param \Cake\ORM\Entity $cashRegister
     */
    public function open($id)
    {
        $current_user = $this->Auth->user();
        $cnt = $this->CashRegisters->find()
            ->where([
                'id !=' => $id,
                'user_id' => $current_user['id'],
                'state IN' => ['OPEN', 'SALE IN PROGRESS']] )
            ->count();
        if ($cnt) {
            $this->Flash->error("Une caisse est déjà ouverte à votre nom");
            $this->redirect(['action' => 'index']);
        }
        $cashRegister = $this->CashRegisters->get($id);
        $data = [
            'state' => 'OPEN',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_title' => trim($current_user['first_name'].' '.$current_user['last_name']),
            'user_id' => $current_user['id'],
        ];
        $cashRegister = $this->CashRegisters->patchEntity($cashRegister, $data, ['validate' => false]);
        $this->CashRegisters->save($cashRegister, ['validate' => false]);
        $this->Flash->success("Caisse {$cashRegister->name} ouverte pour {$current_user['first_name']} {$current_user['last_name']}");
        $this->redirect(['action' => 'idle', $cashRegister->id]);
    }


    /**
     * close()
     *
     * @param int $id
     */
    public function close($id)
    {
        $current_user = $this->Auth->user();
        $cashRegister = $this->CashRegisters->get($id);
        // Ctrl propriaitaire de la caisse
        if ($current_user['id'] != $cashRegister->user_id) {
            $this->Flash->error("Une caisse n'est pas ouverte à votre nom");
            $this->redirect(['action' => 'index']);
        }
        // Ctrl aucune vente en cours
        $cnt = $this->CashRegisters->Sales->find()
            ->where([
                'cash_register_id' => $id,
                'state IN' => ['NEW', 'SALE IN PROGRESS']] )
            ->count();
        if ($cnt) {
            $this->Flash->error("Une vente pour la caisse {$cashRegister->name} n'est pas finalisée");
            return $this->redirect(['action' => 'idle', $id]);
        }
        // Fermeture caisse possible
        $data = [
            'state' => 'CLOSED',
            'ip' => null,
        ];
        $cashRegister = $this->CashRegisters->patchEntity($cashRegister, $data, ['validate' => false]);
        $this->CashRegisters->save($cashRegister, ['validate' => false]);
        $this->Flash->success("Caisse {$cashRegister->name} fermée");
        $this->redirect(['action' => 'index' ]);
    }


    /**
     * mngrSetComplete()
     *
     * @param $id
     *
     * @return \Cake\Http\Response|null
     */
    public function mngrSetComplete($id)
    {
        $cashRegister = $this->CashRegisters->find()
            ->contain('Sales', function(Query $q){
                return $q->select(['Sales.id', 'Sales.name', 'Sales.cash_register_id', 'Sales.state'])
                    ->where(['Sales.state IN' => ['NEW', 'SALE IN PROGRESS']]);
            })
            ->where(['CashRegisters.id' => $id])
            ->first();
        if (empty($cashRegister)) {
            $this->Flash->error('Aucune caisse pour id='.$id);
            return $this->redirect(['action' => 'mngrComplete']);;
        }
        if ($cashRegister->has('sales')) {
            $cnt = count($cashRegister->sales);
            if ($cnt) {
                $this->Flash->error("La caisse &laquo;{$cashRegister->name}&raquo; possède ".($cnt > 1? 'des ventes non finalisée':"une vente non finalisée &laquo;{$cashRegister->sales[0]->name}&raquo"), ['escape' => false]);
                return $this->redirect(['action' => 'mngrComplete']);;
            }
        }
        $cashRegister = $this->CashRegisters->patchEntity($cashRegister, ['state' => 'COMPLETED'], ['validation' => false]);
        if (!$this->CashRegisters->save($cashRegister, ['validation' => false, 'fields' => ['state']])) {
            $this->Flash->error("Echec de la clôture de la caisse &laquo;{$cashRegister->name}&raquo; (1)");
            return $this->redirect(['action' => 'mngrComplete']);;
        }
        // Ctrl mise à jour effectire de la caisse
        $cnt = $this->CashRegisters->find()
            ->where(['id' => $id, 'state' => 'COMPLETED'])
            ->count();
        if ($cnt == 1) {
            $this->Flash->success("La caisse &laquo;{$cashRegister->name}&raquo; est clôturée", ['escape' => false]);
        } else {
            $this->Flash->error("Echec de la clôture de la caisse &laquo;{$cashRegister->name}&raquo; (2)", ['escape' => false]);
        }
        $this->redirect(['action' => 'mngrComplete']);
    }



    /**
     * View method
     *
     * @param string|null $id Cash Register id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $cashRegister = $this->CashRegisters->get($id, [
            'contain' => ['Sales'],
        ]);

        $this->set(compact('cashRegister'));
    }

    /**
     * mngrCashRegistersBalance()
     *
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function mngrCashRegistersBalance()
    {
        $cashRegisters = $this->CashRegisters->find()
            ->enableHydration(false)
            ->all();
            $cr_balance = [];
            foreach ($cashRegisters as $cr) {
                $tmp = ['cash_register' => $cr];
                if (in_array($cr['state'], ['OPEN','SALE IN PROGRESS'])) {
                    $tmp['msg'] = 'Caisse ouverte ou vente en cours : pas de total possible';
                    $tmp['ttls'] = [];
                } else {
                    $q = $this->CashRegisters->Sales->find();
                    $summ = $q->select([
                        'count' => $q->func()->count('*'),
                        'ttl'   => $q->func()->sum('total_price'),
                        'chq'   => $q->func()->sum('pay_chq'),
                        'cash'  => $q->func()->sum('pay_cash'),
                        'other' => $q->func()->sum('pay_other'),
                        ])
                        ->where(['cash_register_id' => $cr['id']])
                        ->enableHydration(false)
                        ->first();
                    foreach ($summ as &$elt) if (empty($elt)) $elt = 0;
                    $tmp['ttls'] = $summ;
                    $tmp['msg'] = '';
                }
                $cr_balance[] = $tmp;
            }
            $pdf_name = 'bilan caisses.pdf';
            $this->CashRegisters->genBalancePDF($cr_balance, 'I', $pdf_name);
            $this->set('pdf_name', $pdf_name);
    }

    /**
     * redirectToSalesProceedIfNeeded()
     *
     * @param \Cake\ORM\Entity $cashRegister
     */
    private function redirectToSalesProceedIfNeeded($cashRegister)
    {
        $current_user = $this->Auth->user();
        if ($cashRegister->user_id != $current_user['id']) {
            // utilisateur courant PAS propriétaire de la caisse
            return; // rien à faire
        }
        if ($cashRegister->state == 'SALE IN PROGRESS') {
            if (!$cashRegister->has('sales')) {
                // Mise a jour de l'état
                $cashRegister = $this->CashRegisters->patchEntity($cashRegister, ['state' => 'OPEN'],['validate' => false]);
                $this->CashRegisters->save($cashRegister, ['validate' => false]);
            } else {
                if($cashRegister->ip == $_SERVER['REMOTE_ADDR']) {
                    foreach ($cashRegister->sales as $sale) {
                        if ($sale->state == 'SALE IN PROGRESS') {
                            // reprise de la vente...
                            $this->Flash->info("Reprise de la vente en cours...");
                            $this->redirect(['controller' => 'Sales', 'action' => 'proceed', $sale->id]);
                            break;
                        }
                    }
                }
            }
        }
    }
}
