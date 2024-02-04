<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;

/**
 * Items Controller
 *
 * @property \App\Model\Table\ItemsTable $Items
 * @method \App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ItemsController extends AppController
{
    /**
     * initialize()
     *
     * @throws \Exception
     */
    public function initialize() :void
    {
        parent::initialize();
        $this->loadComponent('Search.Search', [
            // This is default config. You can modify "actions" as needed to make
            // the PRG component work only for specified methods.
            'actions' => ['mngrIndex']
        ]);
    }

    /**
     * mngrIndex()
     *
     * @param $user
     *
     * @return bool
     */
    public function isAuthorized($user)
    {

        $action = $this->request->getParam('action');
        switch($action) {
            case 'register':
            case 'ajaxSave':
            case 'ajaxDelete':
                return $this->authorizations['Deposits'];
            case 'ajaxSearch':
                return $this->authorizations['MNGR'] || $this->authorizations['Deposits'] || $this->authorizations['CashRegisters'];
            case 'ajaxLock':
            case 'ajaxUnlock':
                return $this->authorizations['CashRegisters'];
            case 'index':
            case 'mngrIndex':
            case 'ajaxReturn':
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


    public function mngrIndex()
    {

        $params = $this->request->getQueryParams();
        if (!empty($params['id'])) {
            $a = [];
            if (preg_match('/\d+[\-](\d+)/', $params['id'], $a)) {
                $params['id'] = $a[1]; // retire n° dépôt
            }
        }
        $query = $this->Items
            ->find('search', ['search' => $params]);

        $this->paginate = ['limit' => 10];
        $this->set('items', $this->paginate($query));
        // Vente cloturée / fin de session
        $cashRegisterTable = \Cake\ORM\TableRegistry::getTableLocator()->get('CashRegisters');
        $cnt = $cashRegisterTable->find()
            ->select(['id', 'name', 'state', 'cash_fund'])
            ->where(['state !=' => 'COMPLETED'])
            ->count();
        $this->set('compteted', $cnt? false : true);

    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Deposits', 'Sales'],
        ];
        $items = $this->paginate($this->Items);

        $this->set(compact('items'));
    }

    /**
     * Index method
     *
     * @param int $deposit_id
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function register($deposit_id)
    {
        $deposit = $this->Items->Deposits->get($deposit_id);
        $items = $this->Items->find()
            ->where(['Items.deposit_id' => $deposit_id])
            ->all();
        if ($deposit->progress != 'EDIT') {
            $this->Flash->warning('Le n° '.$deposit->id.' dépôt est clôturé : Contactez un gestionnaire');
            return $this->redirect(['controller' => 'Deposits', 'action' => 'index']);
        }

        $this->set(compact('items', 'deposit'));
    }


    /**
     * ajaxSave()
     *
     * @param null $id
     */
    public function ajaxSave()
    {
        if (!$this->request->is('post') && !$this->request->is('put')) {
            // rien a faire
            $this->set('item', null);
            $this->set('status', 'NONE');
            $this->set('item_errors', []);
            $this->set('msg', 'Aucune opération effectuée');
            return;
        }
        $data = $this->request->getData();
        if (!$data['id']) {
            $item = $this->Items->newEmptyEntity();
        } else {
            $item = $this->Items->get($data['id']);
            // Ctrl
            if ($item->deposit_id != $data['deposit_id']) {
                // incohérence des données
                $this->set('item', null);
                $this->set('status', 'ERROR');
                $this->set('item_errors', []);
                $this->set('msg', 'Les données semblent incohérentes ! rafraîchissiez cette page (F5)');
                return;
            }
        }
        $item = $this->Items->patchEntity($item, $data);
        $new_item = $this->Items->save($item);
        if ($new_item) {
            $this->set('item', $new_item);
            $this->set('status', 'OK');
            $this->set('item_errors', []);
            $this->set('msg', '');
        } else {
            $this->set('item', null);
            $this->set('status', 'ERROR');
            $this->set('item_errors', $item->getErrors());
            $this->set('msg', 'Des erreurs ont été détectées');
        }
    }


    /**
     * ajaxDelete()
     *
     * @param int $id
     */
    public function ajaxDelete($id)
    {
        if (!is_numeric($id)) {
            $this->set('status', 'ERROR');
            $this->set('msg', 'Argument invalide :'.$id);
            return;
        }
        $id = (int)$id;
        $item = $this->Items->get($id);
        if (!$item) {
            $this->set('status', 'ERROR');
            $this->set('msg', 'Article non trouvé');
            return;
        }
        if ($item->progress != 'EDIT') {
            $this->set('status', 'ERROR');
            $this->set('msg', 'L\'article ne peut pas être supprimé');
            return;
        }
        if ($this->Items->delete($item)) {
            $this->set('status', 'OK');
            $this->set('item_id', $id);
        } else {
            $this->set('status', 'ERROR');
            $this->set('msg', 'Echec de supression de l\'article');
        }
    }


    /**
     * ajaxSearch()
     *
     */
    public function ajaxSearch()
    {
        $data = $this->request->getData();
        if (!empty($data['id'])) {
            // id renseigné : exact match sur Id
            $where = [ 'id' => $data['id'] ];
        } else {
            if (empty($data['ref'])) {
                $this->set('out', ['status' => 'ERR', 'reason' => 'N° non renseigné']);
                return;
            }
            $a = [];
            $ref = &$data['ref'];
            if (is_numeric($ref)) {
                $where = ['OR' => [
                    ['id' => $ref],
                    ['bar_code' => $ref]]
                ];
            } elseif (preg_match('/(\d+)[\-](\d+)/', $ref, $a)) {
                $where = ['OR' => [
                    ['deposit_id' => $a[1], 'id' => $a[2]],
                    ['bar_code' => $ref]]
                ];
            } else {
                $where = ['bar_code' => $ref];
            }
        }
        $where['progress'] = 'ON SALE';
        $items = $this->Items->find()
            ->where($where)
            ->all();
        if (!count($items)) {
            $this->set('out', ['status' => 'NOT FOUND', 'reason' => 'Aucun résultat ne correspond à ce critère']);
        } else {
            $flatten_items = [];
            foreach($items as $item) {
                $flatten_items[] = $item->flat('SALE', $data['h']);
            }
            $this->set('out', ['status' => 'OK', 'items' => $flatten_items]);
        }
    }


    /**
     * ajaxLock()
     *
     * @param int $id
     */
    public function ajaxLock($id)
    {
        $out = [];
        $sale_id = $this->request->getQuery('sale');
        $in_happy_hour_period = $this->request->getQuery('h');
        // Ctrl qualité des données passées en GET
        if ($in_happy_hour_period != 1 && $in_happy_hour_period != 0) {
            $out['msg'] = 'Données invalides';
            $out['status'] = 'ERROR';
            $err_msg = __METHOD__."() données invalides id=[$id], sale_id=[$sale_id] h=[$in_happy_hour_period]";
            \Cake\Log\Log::error($err_msg);
            if (\Cake\Core\Configure::read('debug')) {
                trigger_error($err_msg, E_USER_ERROR);
            }
            $this->set('out', $out);
            return;
        }
        $item = $this->Items->get($id);
        $sale_price = $item->calcSalePrice($in_happy_hour_period);
        // Essaie de poser le LOCK
        $conn = $this->Items->getConnection();
        try {
            $sql = <<< SQL
UPDATE items
SET progress='LOCKED', sale_id=$sale_id, sale_price=$sale_price
WHERE id = $id AND progress='ON SALE'
SQL;
            $result = $conn->execute($sql);
        } catch (Exception $e) {
            $msg = 'Exception caught: '. $e->getMessage();
            $this->log(__METHOD__."() $msg\n  id=$id\nSQL=$sql");
            $this->set('out', ['status'=>'ERROR', 'msg'=>'Echec de mise à jour de l\'article']);
            return;
        }
        // vérification
        $item = $this->Items->get($id);
        if (empty($item)) {
            $this->set('out', ['status'=>'ERROR', 'msg'=>"Article $id non trouvé"]);
            return;
        }
        $out = ['item' => $item->flat('DB')];

        switch($item->progress) {
            case 'LOCKED':
                if ($item->sale_id == $sale_id) {
                    $conn->execute("UPDATE sales SET state='SALE IN PROGRESS' WHERE id={$item->sale_id}");
                    $out['status'] = 'OK';
                } else {
                    $out['status'] = 'ERROR';
                    if ($item->sale_id) {
                        $sale = $this->Item->Sales->get($item->sale_id, ['contain' => 'CashRegisters']);
                        if ($sale) {
                            $out['msg'] = 'Cet article est en cours de vente sur la caisse N° '.$sale->cash_register->name;
                        } else {
                            $out['msg'] = "Cet article est en cours de vente ... caisse non déterminée ({$item->sale_id}): contactez un admin.";
                        }
                    } else {
                        $out['msg'] = 'Cet article est en cours de vente ... caisse non déterminée (null): contactez un admin.';
                    }
                }
                break;
            case 'SOLD':
                $out['status'] = 'ERROR';
                $out['msg'] = 'Cet article est déjà vendu';
                break;
            case 'ON SALE':
                $out['status'] = 'ERROR';
                $out['msg'] = 'L\'enregistrement a échoué';
                break;
            case 'RETURNED':
                $out['status'] = 'ERROR';
                $out['msg'] = 'Cet article ne peut être vendu : retourné';
                break;
            case 'EDIT':
                $out['status'] = 'ERROR';
                $out['msg'] = 'Cet article ne peut être vendu : pas encore en vente';
                break;
            default:
                $out['status'] = 'ERROR';
                $out['msg'] = 'Erreur soft : progress non reconnu ['.$item->progress.']';
                if (\Cake\Core\Configure::read('debug')) {
                	trigger_error($out['msg'], E_USER_ERROR);
                }
        }
        if ($out['status'] == 'ERROR') {
            \Cake\Log\Log::error(__METHOD__."() {$out['msg']}\nItem = ".print_r($item, true));
        }
        $this->set('out', $out);
    }


    /**
     * ajaxUnlock()
     *
     * @param int $id
     */
    public function ajaxUnlock($id)
    {
        $out = [];
        // Essaie de retirer le LOCK
        try {
            $sql = <<< SQL
UPDATE items
SET progress = 'ON SALE', sale_price = Null, sale_id = Null
WHERE id = $id AND progress = 'LOCKED'
SQL;
            $this->Items->getConnection()->execute($sql);
        } catch (Exception $e) {
            $msg = 'Exception caught: '. $e->getMessage();
            $this->log(__METHOD__."() $msg\n  id=$id\nSQL=$sql");
            $this->set('out', ['status'=>'ERROR', 'msg'=>'Echec de mise à jour de l\'article']);
            return;
        }
        // vérification
        $item = $this->Items->get($id);
        if (empty($item)) {
            $this->set('out', ['status'=>'ERROR', 'msg'=>"Article $id non trouvé"]);
            return;
        }

        switch($item->progress) {
            case 'ON SALE':
                $out['status'] = 'OK';
                $out['id'] = $id;
                break;
            case 'LOCKED':
                $out['status'] = 'ERROR';
                $out['msg'] = 'Echec de supression de l\'article';
                break;
            case 'RETURNED':
            case 'EDIT':
                $out['status'] = 'ERROR';
                $out['msg'] = 'Cet article nécessite l\'intervention d\'un adminstrateur !';
                break;
            default:
                $out['status'] = 'ERROR';
                $out['msg'] = 'Erreur soft : progress non reconnu ['.$item->progress.']';
                if (\Cake\Core\Configure::read('debug')) {
                    trigger_error($out['msg'], E_USER_ERROR);
                }
        }
        if ($out['status'] == 'ERROR') {
            \Cake\Log\Log::error(__METHOD__."() {$out['msg']}\nItem = ".print_r($item, true));
        }
        $this->set('out', $out);
    }


    /**
     * ajaxReturn()
     *
     * @param int $id
     */
    public function ajaxReturn($id)
    {
        $item = $this->Items->get($id);
        if ($item->progress != 'SOLD') {
            $this->set('status', 'FATAL');
            $this->set('msg', 'Cet article n\'est pas vendu');
            $this->set('item', $item);
            return;
        }
        $cashRegisterTable = \Cake\ORM\TableRegistry::getTableLocator()->get('CashRegisters');

        if ($this->request->is(['post', 'put'])) {
            $sale_price = $item->sale_price;
            $data = $this->request->getData();
            $data['progress'] = $data['type'] == 'DEFECT'? 'RETURNED' : 'ON SALE';
            $data['return_date'] = FrozenTime::now('Europe/Paris');
            $data['sale_price'] = null;
            $data['debt_amount'] = null;
            $data['sale_id'] = null;
            $cr = $cashRegisterTable->find()
                ->select(['id', 'name', 'cash_fund'])
                ->where(['id' => $data['cash_register_id']])
                ->first();
            if (!$cr) {
                $item->setError('cash_register_id', 'Caisse invalide !');
            }

            $item = $this->Items->patchEntity($item, $data, ['validate' => 'return']);
            if ($this->Items->save($item, ['validate' => 'return', 'fields' => ['progress', 'sale_price', 'debt_amount', 'sale_id']])) {
                // MaJ du fond de caisse
                $cr = $cashRegisterTable->patchEntity($cr, ['cash_fund' => $cr->cash_fund - $sale_price]);
                if ($cashRegisterTable->save($cr, ['fields' => 'cash_fund'])) {
                    $msg = 'La vente de l\'article '.$item->ref.' a été annulée.<br>Restituez '.number_format($sale_price, 2, '.','').' € au client à prendre dans la caisse '.$cr->name;
                    $status = 'SUCCESS';
                } else {
                    $msg = 'La vente de l\'article '.$item->ref.' a été annulée.<br>Mais la caisse '.$cr->name.' n\'a pas été mise à jour ! (débit de ' .number_format($sale_price, 2, '.','').' €';
                    $status = 'FATAL';
                }
            } else {
                $status = 'ERROR';
                $msg = 'Erreur lors de l\'enregistrement du retour';
            }
        } else {
            $status = 'NONE';
            $msg = '';
        }

        $cashRegisters = $cashRegisterTable->find()
            ->select(['id', 'name', 'state', 'cash_fund'])
            ->where(['state !=' => 'COMPLETED'])
            ->all();
        if (!count($cashRegisters)) {
            $this->set('status', 'FATAL');
            $this->set('msg', 'Aucune caisse (non clôturée) trouvée !');
            return;
        }

        $cash_registers_opts = [];
        foreach($cashRegisters as $cashRegister) {
            if ($cashRegister->cash_fund >= $item->sale_price)
            $cash_registers_opts[$cashRegister->id] = $cashRegister->name;
        }

        if (!count($cash_registers_opts)) {
            $this->set('status', 'FATAL');
            $this->set('msg', 'Aucune caisse avec un fond de caisse suffisant trouvée !');
            $this->set('item', $item);
            return;
        }

        $this->set('cash_registers_opts', $cash_registers_opts);
        $this->set('status', $status);
        $this->set('msg', $msg);
        $this->set('item', $item);
    }

    /**
     * View method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * /
    public function view($id = null)
    {
        $item = $this->Items->get($id, [
            'contain' => ['Deposits', 'Sales'],
        ]);

        $this->set(compact('item'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     * /
    public function add()
    {
        $item = $this->Items->newEmptyEntity();
        if ($this->request->is('post')) {
            $item = $this->Items->patchEntity($item, $this->request->getData());
            if ($this->Items->save($item)) {
                $this->Flash->success(__('The item has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The item could not be saved. Please, try again.'));
        }
        $deposits = $this->Items->Deposits->find('list', ['limit' => 200]);
        $sales = $this->Items->Sales->find('list', ['limit' => 200]);
        $this->set(compact('item', 'deposits', 'sales'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * /
    public function edit($id = null)
    {
        $item = $this->Items->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $item = $this->Items->patchEntity($item, $this->request->getData());
            if ($this->Items->save($item)) {
                $this->Flash->success(__('The item has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The item could not be saved. Please, try again.'));
        }
        $deposits = $this->Items->Deposits->find('list', ['limit' => 200]);
        $sales = $this->Items->Sales->find('list', ['limit' => 200]);
        $this->set(compact('item', 'deposits', 'sales'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * /
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $item = $this->Items->get($id);
        if ($this->Items->delete($item)) {
            $this->Flash->success(__('The item has been deleted.'));
        } else {
            $this->Flash->error(__('The item could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
     */
}
