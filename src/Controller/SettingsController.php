<?php
declare(strict_types=1);

namespace App\Controller;


use Cake\I18n\FrozenTime;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SettingsController extends AppController
{

    const max_price = 10000;
    const rate = 50;

    const mandatory_keys = ['colors', 'deposit', 'happy_hour', 'invoice_prefix', 'items', 'organisation', 'pdf'];

    /**
     * isAuthorized()
     *
     * @param null $user
     *
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        return $this->authorizations['MNGR'];
    }



    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $settings = $this->Settings->find()
            ->where(['key_name !=' => 'CONTROL'])
            ->all();

        $this->set(compact('settings'));
    }


    /**
     * ajaxEdit()
     *
     * @param $id
     */
    public function ajaxEdit($id)
    {
        $setting = $this->Settings->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if ($setting->key_name == 'colors') {
                $available_colors = \Cake\Core\Configure::read('items.available_colors');
                if (count($data['colors'])) {
                    $a = [];
                    foreach($data['colors'] as $color) {
                        $a[$color] = $available_colors[$color];
                    }
                    $data['data'] = json_encode($a);
                }
            }
            $setting = $this->Settings->patchEntity($setting, $data);
            if ($this->Settings->save($setting, ['fields' => ['data', 'data_numeric', 'data_date']])) {
                $ret = ['status' => 1, 'msg' => 'Le paramètre a été mis à jour'];
                $this->Settings->setSettings(true); // force la MàJ des settings "ui"
            } else {
                $ret = ['status' => -1, 'msg' => 'Le paramètre n\'a pas été mis à jour'];
            }
        } else {
            $ret = ['status' => 0, 'msg' => ''];
        }
        $this->set('setting', $setting);
        $this->set($ret);
    }


    /**
     * ajaxCheck()
     * Vérifie la cohérence du paramétrage (ui)
     */
    public function ajaxCheck()
    {
        $settings = $this->Settings->readSettings();
        // Ctrl présence clés obligatoires
        foreach (self::mandatory_keys as $mandatory_key) {
            if (!array_key_exists($mandatory_key, $settings)) {
                $this->set('errors', ['Entrée manquante' => $mandatory_key]);
                return;
            }
        }
        $err = [];
        foreach ($settings as $key_name => $value) {
            // Ctrl form num facture
            switch($key_name) {
                case 'invoice_prefix':
                    if (!preg_match('/%\d*d/', $value)) {
                        $err[$key_name] = 'Le format est invalide : doit contenir %d';
                    }
                    break;
                case 'organisation': // array
                    // attendus : event_name, name, rate
                    if (empty($value['event_name'])) {
                        $err['Nom de l\'évènement'] = 'Ce paramètre doit être renseigné';
                    }
                    if (empty($value['name'])) {
                        $err['Nom de l\'organisateur'] = 'Ce paramètre doit être renseigné';
                    }
                    if (!array_key_exists('rate', $value)) {
                        $err['Taux prélevé'] = 'Ce paramètre doit être renseigné';
                    } else {
                        if ($value['rate'] < 0.5 || $value['rate'] > 99.5) {
                            $err['Taux prélevé'] = 'Le taux prélevé est en dehors des limites !';
                        }
                    }
                    break;
                case 'happy_hour': // array
                    if ($value['active']) {
                        $r = $this->checkHappyHour($value);
                        if (!$r['success']) $err[$r['key']] = $r['msg'];
                    }
                    break;
                case 'items':
                    if (empty($value['price'])) {
                        $err['Prix mise en vente'] = 'Prix min et max non valorisés';
                    } else {
                        $r = $this->chkPrices($value['price']);
                        if (!$r['success']) $err = array_merge($err, $r['errs']);
                    }
                    break;
                case 'pdf':
                    if (!empty($value['spool'])) {
                        $r = $this->chkPdfPath($value['spool_path']);
                        if (!$r['success']) {
                            $err['Chemin du répertoire de la file'] = $r['msg'];
                        }
                    }
                    break;
            }

        }
        $this->set('errors', $err);
    }


    /**
     * chkPrices()
     *
     * @param array $prices
     *
     * @return array|bool[]
     */
    private function chkPrices($prices) : array
    {
        $err = [];
        if (empty($prices['min'])) {
            $err['Prix mise en vente minimum'] = 'Ce paramètre doit être renseigné';
        } elseif (!is_numeric($prices['min'])) {
            $err['Prix mise en vente minimum'] = 'Ce paramètre doit être numérique';
        } elseif ($prices['min']< 0 || $prices['min'] > self::max_price) {
            $err['Prix mise en vente minimum'] = 'Valeur en dehors des limites';
        }
        if (empty($prices['max'])) {
            $err['Prix mise en vente maximum'] = 'Ce paramètre doit être renseigné';
        } elseif (!is_numeric($prices['max'])) {
            $err['Prix mise en vente maxnimum'] = 'Ce paramètre doit être numérique';
        } elseif ($prices['max']< 0 || $prices['max'] > self::max_price) {
            $err['Prix mise en vente maximum'] = 'Valeur en dehors des limites';
        }
        if ($prices['min'] >= $prices['max']) {
            $err['Prix mise en vente'] = 'La valeur minimum est suppérieure à la valeur maximum';
        }
        if (!count($err)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errs' => $err];
        }
    }


    /**
     * chkPdfPath()
     *
     * @param string spool_path
     *
     * @return array [ success=>bool, msg=>string ]
     */
    private function chkPdfPath($spool_path)
    {
        if (empty($spool_path)) {
            return['success' => false, 'Vous avez choisi "Envoyer les documents PDF dans a file d\'attente" : le chemin doit être renseigné'];
        }
        if (!preg_match('#/$#', $spool_path)) $spool_path .= '/';
        if (!is_dir($spool_path)) {
            return ['success' => false, 'msg' => 'Le chemin n\'exite pas ou est invalide'];
        }
        $handle = @fopen($spool_path.'test.txt', 'a');
        if(!$handle) {
            return['success' => false, 'Impossible de créer le fichier ['.$spool_path.'test.txt]'];
        }
        fclose($handle);
        return ['success' => true, 'msg' => ''];
    }



    /**
     * checkHappyHour()
     *
     * @param array $params
     *
     * @return array
     */
    private function checkHappyHour($hh_values)
    {
        if ($hh_values['rate'] <= 0) {
            return ['success' => false, 'key' => 'Happy Hour : décote', 'msg' => 'Valeur inférieure ou égale à 0'];
        } elseif ($hh_values['rate'] > self::rate) {
            return ['success' => false, 'key' => 'Happy Hour : décote', 'msg' => 'Valeur supérieure à '.self::rate.' %'];
        }

        if ($hh_values['start']->gte($hh_values['end'])) {
            return ['success' => false, 'key' => 'Happy hour: début', 'msg' => 'la date de début est postérieure à la date de fin'];
        }

        $now = FrozenTime::now();
        if ($hh_values['start']->lt($now)) {
            return ['success' => false, 'key' => 'Happy hour: début', 'msg' => 'la date de début est dépassée'];
        }

        return ['success' => true];
    }


    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Flash->success('Action impossible');
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Setting id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {

        $this->Flash->success('Action impossible');
        return $this->redirect(['action' => 'index']);
    }


    /**
     * Delete method
     *
     * @param string|null $id Setting id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->Flash->success('Action impossible');
        return $this->redirect(['action' => 'index']);
    }
}
