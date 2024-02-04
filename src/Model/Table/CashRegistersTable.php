<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CashRegisters Model
 *
 * @property \App\Model\Table\SalesTable&\Cake\ORM\Association\HasMany $Sales
 *
 * @method \App\Model\Entity\CashRegister newEmptyEntity()
 * @method \App\Model\Entity\CashRegister newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CashRegister[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CashRegister get($primaryKey, $options = [])
 * @method \App\Model\Entity\CashRegister findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CashRegister patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CashRegister[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CashRegister|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CashRegister saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CashRegister[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CashRegister[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CashRegister[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CashRegister[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CashRegistersTable extends Table
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

        $this->setTable('cash_registers');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Sales', [
            'foreignKey' => 'cash_register_id',
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
            ->maxLength('name', 10, 'Maximum 10 caractères')
            ->notEmptyString('name');

        $validator
            ->decimal('cash_fund')
            ->range('cash_fund', [0, 1000], 'Le fond de caisse doit être compris entre 0 et 1000 €')
            ->notEmptyString('cash_fund');

        $validator
            ->scalar('ip')
            ->maxLength('ip', 16)
            ->allowEmptyString('ip');

        $validator
            ->scalar('state')
            ->allowEmptyString('state');

        return $validator;
    }


    /**
     * genBalancePDF()
     *
     * @param $cash_register_data
     * @param string $output_mode
     * @param string $pdf_name
     *
     * @return array|void
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function genBalancePDF($cash_register_data, $output_mode='I', $pdf_name = '')
    {
        $spool = \Cake\Core\Configure::read('ui.pdf.spool');
        if ($spool && $output_mode == 'I') $output_mode = 'F';

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
        $tcpdf->SetCreator(PDF_CREATOR);
        $tcpdf->SetAuthor("Dépôt/Vente by F van de Weerdt - contact@fvdw.fr");
        $tcpdf->SetTitle('Bilan caisses : '. \Cake\Core\Configure::read('ui.organisation.event_name'));
        $tcpdf->SetSubject('Bilan caisses : '. \Cake\Core\Configure::read('ui.organisation.event_name'));

        // Template FPDi
        $template_file = ROOT . DS . 'vendor' . DS . 'vdw' . DS .'tcpdf' . DS . 'pdf_template.pdf';
        $pagecount = $tcpdf->setSourceFile($template_file);
        $tcpdf->tplidx = $tcpdf->importPage(1, '/MediaBox'); // page 1

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        $tcpdf->AddPage();    // required

        $tcpdf->setXY(40, 55);
        $tcpdf->SetFont('','B',15);
        $tcpdf->Cell(130, 0, 'Bilan des caisses @ '.date('d/m/Y H:i'), false, 0, 'C');

        $y = 75;
        $x = 10;
        foreach($cash_register_data as $cr) {
            $tcpdf->SetFont('','B',12);
            $tcpdf->setXY(10, $y);
            if ($cr['ttls']['count'] > 1) {
                $cnt_txt = $cr['ttls']['count']. ' ventes';
            } elseif ($cr['ttls']['count'] == 1) {
                $cnt_txt = '1 vente';
            } else {
                $cnt_txt = 'aucune vente';
            }
            $tcpdf->Cell(60, 0, 'Caisse "'.$cr['cash_register']['name'].'" : '.$cnt_txt, false, 0, 'L');
            $tcpdf->SetFont('','',12);
            $x = 95;

            if (!empty($cr['msg'])) {
                $tcpdf->SetFont('','I',10);
                $tcpdf->setXY($x-10, $y+1);
                $tcpdf->Cell(135, 0, $cr['msg'], '', 0, 'L');
                $y += 16;
                continue;
            }
            // fond de caisse
            // montants cash
            //          cheques
            //          autre

            $tcpdf->setXY($x, $y);
            $tcpdf->Cell(40, 0, 'Fond de caisse', 'B', 0, 'L');
            $tcpdf->setXY($x+40, $y);
            $tcpdf->Cell(40, 0, number_format((float)$cr['cash_register']['cash_fund'],2,'.', '').' €', 'B', 0, 'R');
            $y += 8;
            $tcpdf->setXY($x, $y);
            $tcpdf->Cell(40, 0, 'Espèce', 'B', 0, 'L');
            $tcpdf->setXY($x+40, $y);
            $tcpdf->Cell(40, 0, number_format((float)$cr['ttls']['cash'],2,'.', '').' €', 'B', 0, 'R');
            $y += 8;
            $tcpdf->setXY($x, $y);
            $tcpdf->Cell(40, 0, 'Chèques', 'B', 0, 'L');
            $tcpdf->setXY($x+40, $y);
            $tcpdf->Cell(40, 0, number_format((float)$cr['ttls']['chq'],2,'.', '').' €', 'B', 0, 'R');
            $y += 8;
            $tcpdf->setXY($x, $y);
            $tcpdf->Cell(40, 0, 'Autres', 'B', 0, 'L');
            $tcpdf->setXY($x+40, $y);
            $tcpdf->Cell(40, 0, number_format((float)$cr['ttls']['other'],2,'.', '').' €', 'B', 0, 'R');

            $y += 16;
        }

        if (!$pdf_name) $pdf_name = 'caisses.pdf';
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
}
