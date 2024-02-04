<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Sales Controller
 *
 * @property \App\Model\Table\SalesTable $Sales
 * @method \App\Model\Entity\Sale[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SalesController extends AppController
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
            case 'newSale':
            case 'proceed':
            case 'finalize':
            case 'cancel':
            case 'openCashRegister':
                return $this->authorizations['CashRegisters'];
            case 'index':
            case 'ajaxView':
            case 'ajaxEditCstmInfo':
            case 'invoice':
            case 'invoicePdf':
                return $this->authorizations['CashRegisters'] || $this->authorizations['MNGR'];
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
     * index()
     *
     */
    public function index()
    {
        $where = ['state <>' => 'NEW'];
        if (!$this->authorizations['MNGR']) {
            $where['creator_id'] = $this->current_user['id'];
        }
        $this->paginate = [
            'conditions' => $where,
            'order' => ['created ASC'],
        ];
        $sales = $this->paginate($this->Sales);
        $this->set('sales', $sales);
    }



    /**
     * ajaxView()
     *
     * @param string|null $id Deposit id
     */
    public function ajaxView($id = null)
    {
        $sale = $this->Sales->get($id, [
            'contain' => ['Items'],
        ]);

        $this->set(compact('sale'));
    }


    /**
     * newSale()
     *
     * @param int $cash_register_id
     *
     * @return \Cake\Http\Response|null
     */
    public function newSale($cash_register_id)
    {
        $current_user = $this->Auth->user();
        $cashRegister = $this->Sales->CashRegisters->get($cash_register_id);
        if (!$cashRegister) {
            $err_msg = __METHOD__."() cash_register non trouvé pour id=$cash_register_id";
            \Cake\Log\Log::error($err_msg);
            if (\Cake\Core\Configure::read('debug')) {
                trigger_error($err_msg, E_USER_ERROR);
            }
            $this->Flash->error('Erreur : impossible d\'accèder à cette caisse !');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }
        // OK : utilisateur propriétaire
        if ($cashRegister->user_id != $current_user['id']) {
            $this->Flash->error('Attention, la caisse est ouverte par un autre utilisateur : '.$cashRegister->user_title);
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }
        // Ctrl un seul record Sale avec state=="SALE IN PROGRESS" par caisse
        $sale_id = null;
        $sale = $this->Sales->find()
            ->where([
                'cash_register_id' => $cash_register_id,
                'state IN' => ['NEW', 'SALE IN PROGRESS'],
            ])
            ->first();
        if (!empty($sale)) {
            // Vente en cours sur cette caisse
            if ($sale->creator_id == $current_user['id']) {
                // utilisateur courant est propriétaire de la vente en cours
                $sale_id = $sale->id;
            } else {
                // utilisateur courant != propriétaire de la vente en cours
                $this->Flash->error('Une vente est en cours sur cette caisse au nom de ' . $sale->creator_title . ' ! Vous devriez fermer cette caisse pour permettre à ' . $sale->creator_title . ' de finaliser sa vente.');
                return $this->redirect(['controller' => 'CashRegisters', 'action' => 'idle', $cash_register_id]);
            }
        }
        // Ctrl Etat
        $current_user = $this->Auth->user();
        switch($cashRegister->state) {
            case 'CLOSED':
                $this->Flash->error('La caisse doit être "ouverte" pour enregistrer des ventes');
                return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
            case 'SALE IN PROGRESS':
            case 'OPEN':
                break;
            case 'COMPLETED':
                $this->Flash->error('Cette caisse est clôturée. Plus de vente possible');
                return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
            default:
                $err_msg = __METHOD__."() cashRegister.state non reconnu : [$cashRegister->state]";
                \Cake\Log\Log::error($err_msg);
                if (\Cake\Core\Configure::read('debug')) {
                	trigger_error($err_msg, E_USER_ERROR);
                } else {
                    $this->Flash->error('Cette caisse est dans un état non déterminé : contactez l\'admin.');
                    return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
                }
        }
        // Controle OK => nouvelle vente
        if (!$sale_id) {
            $data = [
                'state'            => 'NEW',
                'cash_register_id' => $cash_register_id,
                'is_happy_hour' => $this->Sales->isHappyHourPeriod(),
            ];
            $sale = $this->Sales->newEntity($data, ['validate' => false]);
            $new_sale = $this->Sales->save($sale, ['validate' => false]);
            $sale_id = $new_sale->id;
        }
        $this->redirect(['action' => 'proceed', $sale_id]);
    }


    /**
     * proceed()
     * Interface Vente : selection articles
     *
     * @param int $id
     */
    public function proceed($id)
    {
        $current_user = $this->Auth->user();
        $sale = $this->Sales->find()
            ->where(['Sales.id' => $id, 'Sales.state IN' =>['NEW', 'SALE IN PROGRESS']])
            ->contain(['CashRegisters', 'Items'])
            ->first();
        if (empty($sale)) {
            $err_msg = __METHOD__."() Sales non trouvé pour id=$id && state IN ('NEW', 'SALE IN PROGRESS')";
            \Cake\Log\Log::error($err_msg);
            $this->Flash->error('Echec : accès à la vente !');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }

        if (empty($sale->cash_register)) {
            $err_msg = __METHOD__."() CashResiter non trouvé pour sale_id={$id}}";
            \Cake\Log\Log::error($err_msg);
            $this->Flash->error('Echec : accès à la caisse !');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }
        if ($sale->cash_register->state == 'OPEN') {
            $sale->cash_register = $this->Sales->CashRegisters->patchEntity($sale->cash_register, ['state' => 'SALE IN PROGRESS'], ['validation' => false]);
            $this->Sales->CashRegisters->save($sale->cash_register, ['validation' => false]);
        }
        $this->set('sale', $sale);
    }


    /**
     * finalize()
     * Enregistre le paiement
     *
     * @param int $id
     */
    public function finalize($id)
    {
        $sale = $this->Sales->find()
            ->where(['Sales.id' => $id, 'Sales.state IN' =>['NEW', 'SALE IN PROGRESS']])
            ->contain(['CashRegisters', 'Items'])
            ->first();
        if (empty($sale)) {
            $err_msg = __METHOD__."() Sales non trouvé pour id=$id && state IN ('NEW', 'SALE IN PROGRESS')";
            \Cake\Log\Log::error($err_msg);
            $this->Flash->error('Echec de finalisation : accès à la vente !');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $sale = $this->Sales->patchEntity($sale, $this->request->getData());
            if (!$sale->getErrors()) {
                // Ctrl montnant
                $items = $this->Sales->Items->find()
                    ->where(['sale_id' => $sale->id, 'progress' => 'LOCKED'])
                    ->select(['sale_price'])
                    ->all();
                $db_ttl = 0;
                foreach ($items as $item) {
                    $db_ttl += round($item->sale_price,2);
                }
                $db_ttl = round($db_ttl,2);
                //
                $post_ttl = round((float)$sale->pay_cash,2) + round((float)$sale->pay_chq,2) + round((float)$sale->pay_other,2);
                if ($post_ttl != $db_ttl) {
                    if (!$post_ttl) {
                        $sale->setError('pay_cash', ['amount' => 'Le montant doit correspondre au total']);
                    } else {
                        if ((float)$sale->pay_cash) $sale->setError('pay_cash', ['amount' => 'Le montant ne correspond pas au total']);
                        if ((float)$sale->pay_chq) $sale->setError('pay_chq', ['amount' => 'Le montant ne correspond pas au total']);
                        if ((float)$sale->pay_other) $sale->setError('pay_other', ['amount' => 'Le montant ne correspond pas au total']);
                    }
                } else {
                    $sql_err = false;
                    $cash = round((float)$sale->pay_cash,2);
                    $chq = round((float)$sale->pay_chq,2);
                    $other = round((float)$sale->pay_other,2);
                    $cr_id = $sale->cash_register_id;
                    $organisation_rate = round( (100 - \Cake\Core\Configure::read('ui.organisation.rate')) /100, 2);
                    // Enregistrement transaction
                    $connection = $this->Sales->getConnection();
                    try {
                        $connection->transactional(function ($conn) use ($id, $db_ttl, $cash, $chq, $other, $cr_id, $organisation_rate) {
                            $conn->execute('UPDATE items SET progress=\'SOLD\', debt_amount=sale_price*? WHERE sale_id=? AND progress=\'LOCKED\'', [$organisation_rate, $id]);
                            $conn->execute('UPDATE sales SET state=\'DONE\', total_price=?, pay_cash=?, pay_chq=?, pay_other=? WHERE id=? AND state IN (\'NEW\',\'SALE IN PROGRESS\')', [$db_ttl, $cash, $chq, $other, $id]);
                            $conn->execute('UPDATE cash_registers SET state=\'OPEN\', cash_fund=cash_fund+? WHERE id=? AND state=\'SALE IN PROGRESS\'', [$cash, $cr_id]);
                        });
                    } catch (Exception $e) {
                        $sql_err = true;
                        $msg = 'Exception caught: '. $e->getMessage();
                        $this->log(__METHOD__."() $msg\n  id=$id");
                        $this->Flash->error('Echec de finalisation : mise à jour de la vente');
                    }
                    if (!$sql_err) {
                        $this->Flash->success('Vente enregistrée');
                        return $this->redirect(['controller' => 'Sales', 'action' => 'invoice', $id]);
                    }
                }
            }
        }
        $this->set('sale', $sale);
    }


    /**
     * invoice()
     *
     * @param $id
     *
     * @return \Cake\Http\Response|null
     */
    public function invoice($id)
    {
        $sale = $this->Sales->find()
            ->where(['Sales.id' => $id, 'Sales.state' =>'DONE'])
            ->contain(['CashRegisters', 'Items'])
            ->first();
        if (empty($sale)) {
            $err_msg = __METHOD__."() Sales non trouvé pour id=$id && state='DONE')";
            \Cake\Log\Log::error($err_msg);
            $this->Flash->error('Facturation : accès à la vente !');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (empty($sale->invoice_num)) {
                // dtm n° facture
                $r = $this->Sales->getNextInvoiceNum(); // retourne [ invoice_num=>"" , invoice_cnt=>int ]
                $data = array_merge($data, $r);
            }
            $sale = $this->Sales->patchEntity($sale, $data, ['validate' => 'invoice']);
            if ($this->Sales->save($sale,  ['validate' => 'invoice'])) {
                $this->Flash->success('Les informations de facturation ont été mises à jour');
            } else {
                $this->Flash->error('Echec de mise à jour des informations de facturation');
            }

        }
        $this->set('sale', $sale);
    }


    /**
     * cancel()
     *
     * @param int $id
     *
     * @return \Cake\Http\Response|null
     */
    public function cancel($id)
    {
        $sale = $this->Sales->find()
            ->select(['id', 'name', 'state', 'cash_register_id'])
            ->where(['Sales.id' => $id])
            ->contain(['Items', 'CashRegisters'])
            ->first();
        if (empty($sale)) {
            $err_msg = __METHOD__."() Record Sales non trouvé pour id=$id";
            \Cake\Log\Log::error($err_msg);
            if (\Cake\Core\Configure::read('debug')) {
            	trigger_error($err_msg, E_USER_ERROR);
            }
            $this->Flash->error('Echec d\'annulation : vente non trouvée');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
        }
        switch ($sale->state) {
            case 'DONE':
                $this->Flash->error('Annulation refusée : la vente est clôturée');
                return $this->redirect(['controller' => 'CashRegisters', 'action' => 'index']);
            case 'NEW':
            case 'SALE IN PROGRESS':
                if ($sale->has('items') && count($sale->items)) {
                    $this->Flash->error('Annulation refusée : des artciles sont liés à cette vente');
                    return $this->redirect(['controller' => 'Sales', 'action' => 'proceed', $id]);
                }
                break;
            case 'CANCELED':
                $this->Flash->info('Annulation déjà effectuée');
                $this->setCashRegisterState($sale->cash_register_id, 'OPEN');
                return $this->redirect(['controller' => 'CashRegisters', 'action' => 'idle', $sale->cash_register_id]);
            default:
                $err_msg = __METHOD__."() valeur de \$sale->state non reconnue [{$sale->state}]";
                \Cake\Log\Log::error($err_msg);
                if (\Cake\Core\Configure::read('debug')) {
                    trigger_error($err_msg, E_USER_ERROR);
                } else {
                    $this->Flash->error('<p>Etat de la caisse indéterminé : contactez l\'adim.</p>');
                    return $this->redirect(['controller' => 'Sales', 'action' => 'proceed', $id]);
                }
        }
        $sale = $this->Sales->patchEntity($sale, ['state' => 'CANCELED'], ['validate' => false]);
        if ($this->Sales->save($sale, ['validate' => false])) {
            $this->setCashRegisterState($sale->cash_register_id, 'OPEN');
            $this->Flash->success('Annulation effectuée');
            return $this->redirect(['controller' => 'CashRegisters', 'action' => 'idle', $sale->cash_register_id]);
        } else {
            $this->Flash->error('Echec d\'annulation ');
            return $this->redirect(['controller' => 'Sales', 'action' => 'proceed', $id]);
        }
    }


    /**
     * invoicePdf()
     *
     * @param int $id
     *
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function invoicePdf($id)
    {
        $pdf_name = $this->Sales->genInvoicePDF($id, 'I');
        $this->set('pdf_name', $pdf_name);
    }



    /**
     * openCashRegister()
     * Ouverture de la caisse et assign à l'utilisateur courant
     *
     * @param \Cake\ORM\Entity $cashRegister
     */
    private function openCashRegister($cashRegister)
    {
        $current_user = $this->Auth->user();
        $data = [
            'state' => 'SALE IN PROGRESS',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_title' => trim($current_user['first_name'].' '.$current_user['last_name']),
            'user_id' => $current_user['id'],
        ];
        $cashRegister = $this->Sales->CashRegisters->patchEntity($cashRegister, $data, ['validate' => false]);
        $this->Sales->CashRegisters->save($cashRegister, ['validate' => false]);
    }


    /**
     * setCashRegisterState()
     *
     * @param int $cash_register_id
     * @param string $state
     */
    private function setCashRegisterState($cash_register_id, $state)
    {
        $cashRegister = $this->Sales->CashRegisters->get($cash_register_id);
        if ($cashRegister->state == $state) return;
        $cashRegister = $this->Sales->CashRegisters->patchEntity($cashRegister, ['state' => $state], ['validate' => false]);
        $this->Sales->CashRegisters->save($cashRegister, ['validate' => false]);
    }



    /**
     * ajaxEdit method
     *
     * @param string|null $id Deposit id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function ajaxEditCstmInfo($id = null)
    {
        $sale = $this->Sales->get($id, [
            'contain' => ['Items'],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (empty($sale->invoice_num)) {
                // dtm n° facture
                $r = $this->Sales->getNextInvoiceNum(); // retourne [ invoice_num=>"" , invoice_cnt=>int ]
                $data = array_merge($data, $r);
            }

            $sale = $this->Sales->patchEntity($sale, $data);
            if ($this->Sales->save($sale, ['fields' => ['invoice_cnt', 'invoice_num', 'customer_info']])) {
                $ret = ['status' => 1, 'msg' => 'L\'adresse de facturation a été mise à jour'];
            } else {
                $ret = ['status' => -1, 'msg' => 'Erreur lors de l\'enregistrement de l\'adresse de facturation'];
            }
        } else {
            $ret = ['status' => 0, 'msg' => ''];
        }
        $this->set('sale' , $sale);
        $this->set($ret);
        $this->render('ajaxView');
    }

}
// EoF
