<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Deposits Controller
 *
 * @property \App\Model\Table\DepositsTable $Deposits
 * @method \App\Model\Entity\Deposit[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DepositsController extends AppController
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
            case 'index':
            case 'close':
            case 'view':
            case 'pdf':
            case 'add':
                return $this->authorizations['Deposits'];
            case 'ajaxView':
            case 'ajaxEdit':
                return $this->authorizations['Deposits'] || $this->authorizations['MNGR'];
            case 'mngrBalance':
            case 'mngrIndex':
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
        $current_user = $this->Auth->user();
        $deposits = $this->Deposits->find()
            ->where(['creator_id' => $current_user['id']])
            ->order(['created DESC'])
            ->limit(10)
            ->all();

        foreach($deposits as $deposit) {
            if ($deposit->progress == 'EDIT' && $deposit->creator_id == $current_user['id'] && $deposit->ip == $_SERVER['REMOTE_ADDR']) {
                $this->Flash->info('Reprise du dépot '.$deposit->id);
                return $this->redirect(['controller' => 'Items', 'action' => 'register', $deposit->id]);
            }
            $deposit->item_cnt = $this->Deposits->Items->find()->where(['deposit_id' => $deposit->id])->count();
        }

        $this->set(compact('deposits'));
    }


    /**
     * mngrIndex method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function mngrIndex()
    {

        $query = $this->Deposits
            // Use the plugins 'search' custom finder and pass in the
            // processed query params
            ->find('search', ['search' => $this->request->getQueryParams()]);
        // ->where(['deleted IS NULL']);
        $this->paginate = ['limit' => 10];
        $deposits = $this->paginate($query);
                foreach($deposits as $deposit) {
            $deposit->item_cnt = $this->Deposits->Items->find()->where(['deposit_id' => $deposit->id])->count();
        }

        $this->set(compact('deposits'));
    }


    /**
     * close()
     *
     * @param $id
     *
     * @return \Cake\Http\Response|null
     */
    public function close($id)
    {
        $deposit = $this->Deposits->get($id, ['contain' => 'Items']);
        if (empty($deposit)) {
            $this->Flash->error('Erreur d\'accès au dépôt N° '.$id);
            return $this->redirect(['controller' => 'Items', 'action' => 'index']);
        }
        if ($deposit->progress != 'EDIT') {
            $this->Flash->info('Ce dépôt est déjà clôturé');
        } else {
            $qry = $this->Deposits->query();
            $qry->update()
                ->set(['progress' => 'CLOSED'])
                ->where(['id' => $id])
                ->execute();
            $qry = $this->Deposits->Items->query();
            $qry->update()
                ->set(['progress' => 'ON SALE'])
                ->where(['deposit_id' => $id])
                ->execute();
            $deposit = $this->Deposits->get($id, ['contain' => 'Items']);
            if ($deposit->progress == 'CLOSED') {
                $this->Flash->success('Le dépot a été clôturé');
            } else {
                $this->Flash->success('Le dépot n\'a pas été clôturé');
                return $this->redirect(['controller' => 'Items', 'action' => 'register', $id]);
            }
        }
        $this->set('deposit', $deposit);
    }


    /**
     * View method
     *
     * @param string|null $id Deposit id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $deposit = $this->Deposits->get($id, [
            'contain' => ['Items'],
        ]);

        $this->set(compact('deposit'));
    }


    /**
     * ajaxView()
     *
     * @param string|null $id Deposit id
     */
    public function ajaxView($id = null)
    {
        $deposit = $this->Deposits->get($id, [
            'contain' => ['Items'],
        ]);

        $this->set(compact('deposit'));
    }


    public function pdf($id)
    {
        $pdf_name = sprintf('depot_%03d.pdf', $id);
        $this->Deposits->genDepositPDF($id, 'I', $pdf_name);
        $this->set('pdf_name', $pdf_name);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $deposit = $this->Deposits->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $deposit = $this->Deposits->patchEntity($deposit, $data);
            $new_deposit = $this->Deposits->save($deposit);
            if ($new_deposit) {
                $this->Flash->success('Le dépot a été créé');
                return $this->redirect(['controller' => 'Items', 'action' => 'register', $new_deposit->id]);
            }
            $this->Flash->error('Erreur lors de l\'enregistrement du dépôt');
        }
        $eregs = $this->Deposits->getEregs();
        $this->set(compact('deposit', 'eregs'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Deposit id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {

        $this->Flash->error('Action impossible');
        $this->redirect(['action' => 'index']);
    }

    /**
     * ajaxEdit method
     *
     * @param string|null $id Deposit id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function ajaxEdit($id = null)
    {
        $deposit = $this->Deposits->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $deposit = $this->Deposits->patchEntity($deposit, $this->request->getData());
            if ($this->Deposits->save($deposit, ['fields' => ['title','phone','email']])) {
                $ret = ['status' => 1, 'msg' => 'L\'entête du dépôt a été mise à jour'];
            } else {
                $ret = ['status' => -1, 'msg' => 'Erreur lors de l\'enregistrement du dépôt'];
            }
        } else {
            $ret = ['status' => 0, 'msg' => ''];
        }
        $eregs = $this->Deposits->getEregs();
        $this->set(compact('deposit', 'eregs'));
        $this->set($ret);
    }

    /**
     * Delete method
     *
     * @param string|null $id Deposit id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $deposit = $this->Deposits->get($id);
        if ($this->Deposits->delete($deposit)) {
            $this->Flash->success(__('The deposit has been deleted.'));
        } else {
            $this->Flash->error(__('The deposit could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * balance()
     *
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function mngrBalance()
    {
        $cnt_locked = $this->Deposits->Items->find()
            ->where(['progress IN' => ['LOCKED','EDIT'] ])
            ->count();
        if ($cnt_locked) {
            // Des articles sont "en cours d'édition" ou "en cours de vente"
            // --> pas possible de faire la balance !
        }
        $pdf_name = 'balance_'.date('dMY_H\hi').'.pdf';
        $this->Deposits->getBalancePDF('I', $pdf_name);
        $this->set('pdf_name', $pdf_name);
        $this->render('pdf');

    }

}
