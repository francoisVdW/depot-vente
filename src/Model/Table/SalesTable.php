<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Sales Model
 *
 * @property \App\Model\Table\CashRegistersTable&\Cake\ORM\Association\BelongsTo $CashRegisters
 * @property \App\Model\Table\CreatorsTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\ItemsTable&\Cake\ORM\Association\HasMany $Items
 *
 * @method \App\Model\Entity\Sale newEmptyEntity()
 * @method \App\Model\Entity\Sale newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Sale[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Sale get($primaryKey, $options = [])
 * @method \App\Model\Entity\Sale findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Sale patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Sale[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Sale|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Sale saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Sale[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Sale[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Sale[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Sale[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SalesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('sales');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('TimeStampOwner');
/*
        // Add the search behaviour to your table
        $this->addBehavior('Search.Search');
        // Setup search filter using search manager
        $this->searchManager()
            ->add('id', 'Search.Like', [
                'before' => false,
                'after' => true,
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'field' => ['id']
            ])
            ->add('vet', 'Search.Like', [
                'before' => false,
                'after' => true,
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'field' => ['Users.last_name']
            ]);
*/
        $this->belongsTo('CashRegisters', [
            'foreignKey' => 'cash_register_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Items', [
            'foreignKey' => 'sale_id',
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
            ->scalar('name')
            ->maxLength('name', 20)
            ->allowEmptyString('name');

        $validator
            ->scalar('customer_info')
            ->allowEmptyString('customer_info');

        $validator
            ->decimal('pay_chq', null, 'Le montant est invalide')
            ->range('pay_chq', [0,500], 'Montant du chèque hors limites > 500 €')
            ->allowEmptyString('pay_chq');

        $validator
            ->decimal('pay_cash', null, 'Le montant est invalide')
            ->allowEmptyString('pay_cash');

        $validator
            ->decimal('pay_other', null, 'Le montant est invalide')
            ->allowEmptyString('pay_other');

        $validator
            ->scalar('creator_title')
            ->maxLength('creator_title', 30)
            ->allowEmptyString('creator_title');

        return $validator;
    }


    /**
     * validationInvoice()
     *
     * @param \Cake\Validation\Validator $validator
     *
     * @return \Cake\Validation\Validator
     */
    public function validationInvoice(Validator $validator): Validator
    {

        $validator
            ->scalar('customer_info')
            ->notBlank('customer_info', 'Les coordonnées du client sont requises')
            ->notEmptyString('customer_info', 'Les coordonnées du client sont requises')
            ->maxLength('customer_info', 520, 'Les coordonnées du client sont trop longues');

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
        $rules->add($rules->existsIn(['cash_register_id'], 'CashRegisters'), ['errorField' => 'name']);

        return $rules;
    }


    /**
     * getNextInvoiceNum()
     *
     * @return array
     */
    public function getNextInvoiceNum()
    {
        $invoice_prefix = \Cake\Core\Configure::read('ui.invoice_prefix');
        $query = $this->find();
        $sale = $query
            ->select(['max_invoice_cnt' => $query->func()->max('invoice_cnt')])
            ->first();
        $max = empty($sale)? 1 : $sale->max_invoice_cnt + 1;

        return ['invoice_num' => sprintf($invoice_prefix, $max), 'invoice_cnt' => $max];
    }


    /**
     * genPDF()
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
    public function genInvoicePDF($id, $output_mode='I')
    {
        $spool = \Cake\Core\Configure::read('ui.pdf.spool');
        if ($spool && $output_mode == 'I') $output_mode = 'F';

        $sale = $this->get($id, ['contain' => ['Items']]);

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
        $pdf_data['cfg'] = \Cake\Core\Configure::read('invoice_pdf_pattern');
        $pdf_data['section_name'] = 'items';
        //
        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        // ajustement des données
        foreach($sale->items as $item) {
            $pdf_data['items'][] = [
                // cf config/depot_vente_cfg.php
                'ref' => $item->ref,
                'name' => $item->name,
                'sale_price' => number_format((float)$item->sale_price, 2, '.', '')
            ];
        }
        $pdf_data['items'][] = [
            'ttl' => number_format((float)$sale->total_price, 2, '.', '')
        ];
        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        $pdf_data['pdf_properties'] = [
            'title' => 'Facture N° '. $sale->invoice_num,
            'subject' => 'Facture N° '. $sale->invoice_num,
        ];
        // ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~ ~~
        $pdf_data['fixed'] = [ // cf 'head'
            'invoice_num' => $sale->invoice_num,
            'customer_info' => $sale->customer_info,
            'created' => $sale->sCreated,
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

        $pdf_name = str_replace('-', '_', $sale->invoice_num).'.pdf';
        if ($output_mode == 'I') {
            $tcpdf->Output(null, 'I');    // Envoi vers navigateur
            exit;
        } elseif ($output_mode == 'S') {
            $buffer_pdf = $tcpdf->Output(null, 'S');    // Envoi vers buffer
            return ['success' => true, 'msg' => '', 'buffer' => $buffer_pdf];
        } elseif ($output_mode == 'F') {
            $spool_path = \Cake\Core\Configure::read('ui.pdf.spool_path') ;
            if (!preg_match('#'.DS.'$#', $spool_path)) $spool_path .= DS;
            $tcpdf->Output($spool_path.$pdf_name, 'F');    // Envoi vers fichier
            return $pdf_name;
        }
    }


    /**
     * happyHourInfo()
     *
     * @return array
     */
    public static function happyHourInfo()
    {
        if (!self::happyHourActive()) return ['active' => false];
        $ret = ['active' => true];
        $hh_defs = \Cake\Core\Configure::read('ui.happy_hour');
        $ret['fmt_start'] = $hh_defs['start']->format('d/m/Y <\s\p\a\n \c\l\a\s\s="\h\m">H:i</span>');
        if (!empty($hh_defs['end'])) {
            $ret['fmt_end'] = $hh_defs['end']->format('d/m/Y <\s\p\a\n \c\l\a\s\s="\h\m">H:i</span>');
        }

        $ret['in'] = self::isHappyHourPeriod();

        $now = FrozenTime::now();
        // comment
        if ($ret['in']) {
            // temps restant
            $diff = $now->diff($hh_defs['end']);
            $d = [];
            if ($diff->d > 1) $d[] = ' '.$diff->d.' jours';
            elseif ($diff->d == 1) $d[] = ' 1 jour';
            if ($diff->h) $d[] = $diff->h.' h';
            $min = sprintf('%02d', ($diff->s > 30 && $diff->i != 59)? $diff->i+1: $diff->i);
            $d[] = "$min min";
            $ret['comment'] = "Se termine dans ".join(' ', $d);
        } elseif ($now->lt( $hh_defs['start'])) {
            $diff = $now->diff($hh_defs['start']);
            $d = [];
            if ($diff->d > 1) $d[] = ' ' . $diff->d . ' jours';
            elseif ($diff->d == 1) $d[] = ' 1 jour';
            if ($diff->h) $d[] = $diff->h . ' h';
            if ($diff->i) $d[] = ($diff->s > 30 && $diff->i != 59) ? $diff->i + 1 : $diff->i . ' min';
            $ret['comment'] = "Commence dans " . join(' ', $d);
        } elseif ($now->gt( $hh_defs['end'] )) {
            $ret['comment'] = "Terminé";
        } else {
            $ret['comment'] = "?";
        }
        return $ret;
    }


    /**
     * isHappyHourPeriod()
     *
     * @return bool
     */
    public static function isHappyHourPeriod()
    {
        $hh_defs = \Cake\Core\Configure::read('ui.happy_hour');
        if (!$hh_defs['active']) return false;
        $now = FrozenTime::now('Europe/Paris');
        if (!empty($hh_defs['end'])) {
            return $now->between($hh_defs['start'], $hh_defs['end']);
        }
        if ($now->lt($hh_defs['start'])) {
            return false;
        } else {
            if (!empty($hh_defs['end'])) {
                if ($now->lte( $hh_defs['end'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }


    /**
     * happyHourExists()
     *
     * @return bool
     */
    public static function happyHourActive()
    {
        $hh_defs = \Cake\Core\Configure::read('ui.happy_hour');
        return $hh_defs['active']? true : false;
    }


    /**
     * beforeSave()
     *
     * @param \Cake\Event\Event $event
     * @param $entity
     * @param array $options
     */
    public function afterSave(\Cake\Event\Event $event, $entity, $options = [])
    {
        if (empty($entity->name)) {
            $qry = $this->query();
            $qry->update()
                ->set(['name' => 'Vente '.$entity->id])
                ->where(['id' => $entity->id])
                ->execute();
        }
    }

}
