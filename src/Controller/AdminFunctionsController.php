<?php
declare(strict_types=1);

namespace App\Controller;
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 30/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 */
class AdminFunctionsController extends AppController
{

    /**
     * initialize()
     *
     */
    public function initialize() :void
    {
        parent::initialize();
        $this->modelClass = false;
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
        return $this->authorizations['ADMIN'];
    }


    public function home()
    {

    }

    public function reset()
    {
        $this->loadModel('CashRegisters');
        $this->loadModel('Deposits');
        $this->loadModel('Users');
        $reject_reasons = [];
        $now = \Cake\I18n\FrozenTime::now();

        // Depots
        $ttl_deposit = $this->Deposits->find()->count();
        $last_deposit = $this->Deposits->find()
            ->select(['id', 'created'])
            ->order(['created DESC'])
            ->first();
        // nb de jours depuis dernier dépôt
        if ($last_deposit) $days_last_deposit = $last_deposit->created->diffInDays($now);
        else $days_last_deposit = 365;
        if ($last_deposit && $days_last_deposit < 10) {
            $cnt_deposit = $this->Deposits->find()
                ->where(['progress !=' => 'CLOSED'])
                ->count();
            if ($cnt_deposit) {
                $reject_reasons[] = $ttl_deposit . ' dépôt(s) non finalisée(s)';
            }
        }

        // Ventes
        $ttl_sale = $this->CashRegisters->Sales->find()->count();
        $last_sale = $this->CashRegisters->Sales->find()
            ->select(['id','name', 'created'])
            ->order(['created DESC'])
            ->first();
        // nb de jours depuis derniere vente
        if ($last_sale) $days_last_sale = $last_sale->created->diffInDays($now);
        else $days_last_sale = 365;
        if ($last_sale && $days_last_sale < 10) {
            $cnt_sale = $this->CashRegisters->Sales->find()
                ->where(['state IN' => ['NEW', 'SALE IN PROGRESS']])
                ->count();
            if ($cnt_sale) {
                $reject_reasons[] = $cnt_sale . ' vente(s) non finalisée(s)';
            }
        }
        // items
        $ttl_item = $this->CashRegisters->Sales->Items->find()->count();
        // users

        $ttl_user = $this->Users->find()->count();

        // Caisses
        $cashRegisters = $this->CashRegisters->find()
            ->where(['state !='  => 'COMPLETED'])
            ->all();
        foreach($cashRegisters as $cr) {
            $reject_reasons[] = 'La caisse '.$cr->name.' n\'est pas clôturée';
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            if (count($reject_reasons)) {
                $this->Flash->error('Remise à zéro refusée, des caisses restent ouvertes et/ou des dépôts et/ou ventes ne sont pas finalisés');
            } else {
                $data = $this->request->getData();
// =================================================================
// DEBUG VDW
$mmm = __METHOD__;
echo '<div style="clear:both;display:block;padding:3px:margin:3px;border: 1px #000 solid;">';
if (!empty($mmm)) echo $mmm."() ";
echo  __FILE__.' : '.__LINE__;
echo '<pre style="font-size:11px;color:#000;background-color:#ffff60;overflow-x:scroll">';
print_r($data);
echo '</pre></div>';
// exit;
// =================================================================

                // Ctrl passwd
                $current_user = $this->Auth->user();
                $cnt = $this->Users->find()->where(['id' => $current_user['id'], 'password' => md5($data['passwd']), 'roles_json LIKE' => '%"ADMIN"%'])->count();
                if ($cnt != 1) {
                    $this->Flash->error('votre mot de passe n\'est pas correct !');
                } else {
                    $connection = $this->CashRegisters->getConnection();
                    try {
                        $connection->transactional(function ($conn)  {
                            $conn->execute('TRUNCATE TABLE deposits');
                            $conn->execute('TRUNCATE TABLE items');
                            $conn->execute('TRUNCATE TABLE sales');
                        });
                        $sql_err = false;
                    } catch (Exception $e) {
                        $sql_err = true;
                        $msg = 'Exception caught: '. $e->getMessage();
                        $this->log(__METHOD__."() $msg");
                        $this->Flash->error('Echec de la remise à zéro');
                    }
                    if (!$sql_err) {
                        if ($data['reset_cash_register']) {
                            $qry = $this->CashRegisters->query();
                            $qry->update()
                                ->set(['cash_fund' => 0, 'ip' => null, 'user_id' => null, 'user_title' => null, 'state' => 'CLOSED'])
                                ->where(['state' => 'COMPLETED'])
                                ->execute();
                        }
                        if ($data['drop_users']) {
                            $this->Users->deleteAll(['id !=' => $current_user['id']]);
                        }
                        $this->Flash->success('Remise à zéro effectuée');
                       // return $this->redirect(['controller' => 'AdminFunctions', 'action' => 'reset']);
                    }
                }
            }
        }
        $this->set('cnt_sale', $ttl_sale);
        $this->set('cnt_deposit', $ttl_deposit);
        $this->set('cnt_item', $ttl_item);
        $this->set('cnt_user', $ttl_user);

        $this->set('days_last_sale', $days_last_sale);
        $this->set('days_last_deposit', $days_last_deposit);
        $this->set('reject_reasons', $reject_reasons);
    }
}
