<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Items Model
 *
 * @property \App\Model\Table\DepositsTable&\Cake\ORM\Association\BelongsTo $Deposits
 * @property \App\Model\Table\SalesTable&\Cake\ORM\Association\BelongsTo $Sales
 *
 * @method \App\Model\Entity\Item newEmptyEntity()
 * @method \App\Model\Entity\Item newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Item[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Item get($primaryKey, $options = [])
 * @method \App\Model\Entity\Item findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Item patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Item[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Item|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Item saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ItemsTable extends Table
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

        $this->setTable('items');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('TimeStampOwner');
        // Add the search behaviour to your table
        $this->addBehavior('Search.Search');
        // Setup search filter using search manager
        $this->searchManager()
            ->add('id', 'Search.Value', [
                'fields' => ['id']
            ])
            ->add('name', 'Search.Like', [
                'before' => true,
                'after' => true,
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['Items.name', 'Items.mfr_part_no','Items.bar_code']
            ]);

        $this->belongsTo('Deposits', [
            'foreignKey' => 'deposit_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Sales', [
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
        $price_range = [
            \Cake\Core\Configure::read('ui.items.price.min'),
            \Cake\Core\Configure::read('ui.items.price.max'),
        ];
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 250, 'La désignation est limitée à 250 caractères')
            ->notBlank('name', 'La désignation est requise')
            ->notEmptyString('name', 'La désignation est requise');

        $validator
            ->scalar('bar_code')
            ->maxLength('bar_code', 50, 'le code à barre est limité à 50 positions')
            ->allowEmptyString('bar_code');

        $validator
            ->scalar('mfr_part_no')
            ->maxLength('mfr_part_no', 50, 'la référence fabricant est limitée à 50 positions')
            ->allowEmptyString('mfr_part_no');

        $validator
            ->decimal('requested_price', null,'Le montant est invalide')
            ->range('requested_price', $price_range, 'Le prix est en dehors des limites ('.\Cake\Core\Configure::read('ui.items.price.min').', '.\Cake\Core\Configure::read('ui.items.price.max').')')
            ->notEmptyString('requested_price', 'Le prix de mise en vente est requis');

        $validator
            ->decimal('sale_price', null,'Le montant est invalide')
            ->allowEmptyString('sale_price');

        $validator
            ->boolean('happy_hour')
            ->notEmptyString('happy_hour');

        $validator
            ->dateTime('return_date')
            ->allowEmptyDateTime('return_date');

        return $validator;
    }



    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationReturn(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('return_cause')
            ->maxLength('return_cause', 254, 'Le motif est limité à 250 caractères')
            ->add('return_cause', 'custom', [
                'rule' => function ($value, $context)  {

                    if ($context['data']['progress'] == 'RETURNED') {
                        if (!empty($value)) $value = trim($value);
                        return $value? true:false;
                    } else {
                        return true;
                    }
                },
                'message' => 'Indiquez la raison du retour de l\'article défectueux'
            ]);

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
        $rules->add($rules->isUnique(['bar_code'], 'Ce code barre est déjà utilisé'));
        $rules->add($rules->existsIn(['deposit_id'], 'Deposits'), ['errorField' => 'deposit_id']);
        return $rules;
    }


    /**
     * beforeSave()
     *
     * @param \Cake\Event\Event $event
     * @param $entity
     * @param array $options
     */
    public function beforeSave(\Cake\Event\Event $event, $entity, $options = [])
    {
        if (empty($entity->bar_code)) {
            $entity->bar_code = null;
        } elseif (!trim($entity->bar_code)) {
            $entity->bar_code = null;
        }
        if (empty($entity->mfr_part_no)) {
            $entity->mfr_part_no = null;
        } elseif (!trim($entity->mfr_part_no)) {
            $entity->mfr_part_no = null;
        }
    }

}
