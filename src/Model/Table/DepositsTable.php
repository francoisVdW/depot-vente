<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Deposits Model
 *
 * @property \App\Model\Table\CreatorsTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\ItemsTable&\Cake\ORM\Association\HasMany $Items
 *
 * @method \App\Model\Entity\Deposit newEmptyEntity()
 * @method \App\Model\Entity\Deposit newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Deposit[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Deposit get($primaryKey, $options = [])
 * @method \App\Model\Entity\Deposit findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Deposit patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Deposit[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Deposit|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Deposit saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Deposit[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Deposit[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Deposit[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Deposit[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DepositsTable extends Table
{
    const EREG_TITLE = '/^[A-Za-z][A-Za-z \-\.çôêéèëîï]+$/';
    const EREG_PHONE = '/^0[\d \-]{7,}$/';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('deposits');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('TimeStampOwner');

        // Add the search behaviour to your table
        $this->addBehavior('Search.Search');
        // Setup search filter using search manager
        $this->searchManager()
            ->add('id', 'Search.Value', [
                'fields' => ['id']
            ])
            ->add('title', 'Search.Like', [
                'before' => true,
                'after' => true,
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['title']
            ]);

        $this->hasMany('Items', [
            'foreignKey' => 'deposit_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('title')
            ->notBlank('title', 'Ce champ est obligatoire')
            ->regex('title', self::EREG_TITLE, 'Le nom contient des caractères invalides')
            ->maxLength('title', 50);

        $validator
            ->scalar('phone')
            ->notBlank('phone', 'Ce champ est obligatoire')
            ->regex('phone', self::EREG_PHONE, 'Le n° de téléphone contient des caractères invalides')
            ->maxLength('phone', 20);

        $validator
            ->email('email')
            ->allowEmptyString('email');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }

    /**
     * getEregs()
     *
     * @return string[]
     */
    public function getEregs()
    {
        return [
            'title' => self::EREG_TITLE,
            'phone' => self::EREG_PHONE,
        ];
    }



    public function chkDeposit($id)
    {
        $this->find()
            ->contain(['Items' => ['Sales' => ['CashRegisters']]])
            ->where(['id' => $id])
            ->first();
    }

    /**
     * genDepositPDF()
     *
     * @param int $id
     * @param string $output_mode I|S
     * @param string $pdf_name
     *
     * @return array
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function genDepositPDF($id, $output_mode='I', $pdf_name = '')
    {
        $spool = \Cake\Core\Configure::read('ui.pdf.spool');
        if ($spool && $output_mode == 'I') $output_mode = 'F';


        $deposit = $this->find()
            ->contain('Items', function (Query $q) { return $q->where(['progress' => 'ON SALE']); })
            ->where(['Deposits.id' => $id])
            ->first();
//        $deposit = $this->get($id, ['contain' => ['Items']]);

        require_once(ROOT . DS . 'vendor' . DS . 'vdw' . DS . 'tcpdf' . DS . 'DocumentPDF.php');

        $tcpdf = new \documentPDF(\Cake\Core\Configure::read('debug'));

        if (empty($tcpdf)) {
            $err_msg = __METHOD__ . "() Erreur creation obj DocumentPDF";
            \Cake\Log\Log::error($err_msg);
            if (\Cake\Core\Configure::read('debug')) {
                trigger_error($err_msg, E_USER_ERROR);
            }
            exit;
        }

        $pdf_data = [];
        $pdf_data['cfg'] = \Cake\Core\Configure::read('deposit_pdf_pattern');
        $pdf_data['section_name'] = 'items';
        //
        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        // ajustement des données
        foreach($deposit->items as $item) {
            $pdf_data['items'][] = [
                // cf config/depot_vente_cfg.php
                'ref' => $item->ref,
                'name' => $item->name,
                'requested_price' => $item->requested_price,
                'happy_hour' => $item->happy_hour? ':-)':'',
            ];
        }
        $pdf_data['items'][] = [
            // cf config/depot_vente_cfg.php
            'ref' => '',
            'name' => 'TOTAL : '.count($deposit->items).' article'.(count($deposit->items) > 1? 's' :''),
            'requested_price' => '',
            'happy_hour' => '',
        ];
        $pdf_data['items'][] = [
            'note' => "\n".\Cake\Core\Configure::read('ui.deposit.pdf.note'),
        ];

        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        $pdf_data['pdf_properties'] = [
            'title' => 'Dépôt N° '. $deposit->id,
            'subject' => 'Dépôt N° '. $deposit->id
        ];
        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        $pdf_data['fixed'] = [ // cf 'head'
            'dest' => 'Organisateur',
            'id' => $deposit->id,
            'title' => $deposit->title,
            'tel' => $deposit->numTel,
            'email' => $deposit->email,
            'created' => $deposit->sCreated,
            'creator' => $deposit->creator_title,
        ];


        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        // ajustement des données et assigne les valeurs dans objet $tcpdf
        $tcpdf->setDataAndCfg($pdf_data);
        // Template FPDi
        $pagecount = $tcpdf->setSourceFile($tcpdf->template_file);
        $tcpdf->tplidx = $tcpdf->importPage(1, '/MediaBox'); // page 1

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        $tcpdf->AddPage();    // required
        $tcpdf->process();
        // Signature du déposant
        $tcpdf->SetDrawColor(80,80,80);
        $tcpdf->setXY(130, 240);
        $tcpdf->MultiCell(70,45,'Signature du déposant', 1, 'C');

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        $pdf_data['fixed']['dest'] = 'Déposant';
        $tcpdf->setDataAndCfg($pdf_data);
        $tcpdf->AddPage();
        $tcpdf->process();


        if (!$pdf_name) $pdf_name = sprintf('depot_%03d', $deposit->id);
        if ($output_mode == 'I') {
            $tcpdf->Output($pdf_name, 'I');    // Envoi vers navigateur
            exit;
        } elseif ($output_mode == 'S') {
            $buffer_pdf = $tcpdf->Output(null, 'S');    // Envoi vers buffer
            return ['success' => true, 'msg' => '', 'buffer' => $buffer_pdf];
        } elseif ($output_mode == 'F') {
            // envoi vers spool
            $spool_path = \Cake\Core\Configure::read('ui.pdf.spool_path') ;
            if (!preg_match('#'.DS.'$#', $spool_path)) $spool_path .= DS;
            $tcpdf->Output($spool_path.$pdf_name, 'F');    // Envoi vers fichier
        }
    }


    /**
     * getBalancePDF()
     * Génère un doc PDF avec tous les dépôts
     *
     * @param string $output_mode I|S
     *
     * @return array
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function getBalancePDF($output_mode = 'I', $pdf_name = '')
    {
        $spool = \Cake\Core\Configure::read('ui.pdf.spool');
        if ($spool && $output_mode == 'I') $output_mode = 'F';

        require_once(ROOT . DS . 'vendor' . DS . 'vdw' . DS . 'tcpdf' . DS . 'DocumentPDF.php');

        $tcpdf = new \documentPDF(\Cake\Core\Configure::read('debug'));

        if (empty($tcpdf)) {
            $err_msg = __METHOD__ . "() Erreur creation obj AnalysisOrderPDF";
            \Cake\Log\Log::error($err_msg);
            if (\Cake\Core\Configure::read('debug')) {
                trigger_error($err_msg, E_USER_ERROR);
            }
            exit;
        }
        $pdf_data = [];
        $pdf_data['cfg'] = \Cake\Core\Configure::read('balance_pdf_pattern');
        $pdf_data['section_name'] = 'items';
        $tcpdf->setDataAndCfg($pdf_data);

        // Template FPDi
        $pagecount = $tcpdf->setSourceFile($tcpdf->template_file);
        $tcpdf->tplidx = $tcpdf->importPage(1, '/MediaBox'); // page 1


        $deposits = $this->find()
            ->where(['Deposits.progress' => 'CLOSED'])
            ->order(['id'])
            ->enableHydration(false)
            ->all();

        foreach($deposits as $deposit) {
            $pdf_data['pdf_properties'] = [
                'title' => 'Balance des dépôts',
                'subject' => 'Balance des dépôts',
            ];
            // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
            $pdf_data['fixed'] = [ // cf 'head'
                'dest' => 'Organisateur',
                'id' => $deposit['id'],
                'title' => $deposit['title'],
                'tel' => $deposit['numTel'],
                'email' => $deposit['email'],
            ];
            // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
            // items
            $items = $this->Items->find()
                ->where(['deposit_id' => $deposit['id'], 'progress IN' => ['SOLD', 'ON SALE', 'RETURNED']])
                ->order(['id'])
                ->enableHydration(false)
                ->all();
            if (!count($items)) continue;
            // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
            // ajustement des données
            $ttl_debt = 0;
            $not_sold_items = 0;
            $sold_items = 0;
            $pdf_data['items'] = [];
            foreach($items as $item) {
                if ($item['progress'] == 'SOLD') {
                    $status = 'VENDU';
                    $debt_amount = number_format($item['debt_amount'], 2, ',','');
                    $happy_hrs = ($item['happy_hour'] && $item['requested_price'] > $item['sale_price'])? ':-)':'';
                    $sold_items++;
                    $ttl_debt += $item['debt_amount'];
                    $ref = '';
                } else{
                    if ($item['progress'] == 'RETURNED') {
                        $status = 'RETOUR';
                    } else {
                        $status = '';
                    }
                    $ref = $deposit['id'].'-'.$item['id'];
                    $debt_amount = '';
                    $happy_hrs = '';
                    $not_sold_items++;
                }
                $pdf_data['items'][] = [
                    // cf config/depot_vente_cfg.php
                    'status' => $status,
                    'ref' => $ref,
                    'name' => $item['name'],
                    'debt_amount' => $debt_amount,
                    'happy_hour_applied' => $happy_hrs,
                ];
                if ($item['progress'] == 'RETURNED') {
                    $pdf_data['items'][] = ['return' => 'Motif du retour : '.$item['return_cause']];
                }
            }
            if ($not_sold_items > 1) {
                $msg_ttl = "$not_sold_items articles à restituer";
            } elseif($not_sold_items == 1) {
                $msg_ttl = "Un article à restituer";
            } else {
                $msg_ttl = "Aucun article à restituer";
            }
            $msg_ttl .= '  -  Somme due : ' .number_format($ttl_debt, 2,',', ''). ' €';
            $pdf_data['items'][] = ['ttl' => '']; // passe une ligne
            $pdf_data['items'][] = ['ttl' => $msg_ttl];
            // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
            // ajustement des données et assigne les valeurs dans objet $tcpdf
            $tcpdf->setDataAndCfg($pdf_data);
            $tcpdf->AddPage();    // required
            $tcpdf->process();
        }

        if (!$pdf_name) $pdf_name = 'balance_'.date('dMY_H\hi').'.pdf';
        if ($output_mode == 'I') {
            $tcpdf->Output($pdf_name, 'I');    // Envoi vers navigateur
            exit;
        } elseif ($output_mode == 'S') {
            $buffer_pdf = $tcpdf->Output(null, 'S');    // Envoi vers buffer
            return ['success' => true, 'msg' => '', 'buffer' => $buffer_pdf];
        } elseif ($output_mode == 'F') {
            $spool_path = \Cake\Core\Configure::read('ui.pdf.spool_path') ;
            if (!preg_match('#'.DS.'$#', $spool_path)) $spool_path .= DS;
            $tcpdf->Output($spool_path.$pdf_name, 'F');    // Envoi vers fichier
        }

    }
    // ================== Callbacks ===========================================
    /**
     * beforeSave()
     *
     * @param \Cake\Event\Event $event
     * @param $entity
     * @param array $options
     *
     * @return bool
     */
    public function beforeSave(\Cake\Event\Event $event, $entity, $options = [])
    {
        if (!empty($entity->phone)) {
            $entity->phone = preg_replace('/[^\d]/', '', $entity->phone);
        }
        if (!empty($entity->email)) {
            $entity->email = trim(strtolower($entity->email));
        }
        return true;
    }
}
