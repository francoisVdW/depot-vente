<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 * @property \App\Model\Table\ModifiersTable&\Cake\ORM\Association\BelongsTo $Modifiers
 *
 * @method \App\Model\Entity\Setting newEmptyEntity()
 * @method \App\Model\Entity\Setting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Setting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Setting get($primaryKey, $options = [])
 * @method \App\Model\Entity\Setting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Setting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Setting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Setting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Setting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SettingsTable extends Table
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

        $this->setTable('settings');
        $this->setDisplayField('key');
        $this->setPrimaryKey('id');

        $this->addBehavior('TimeStampOwner');

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
            ->scalar('data')
            ->notBlank('data', 'Ce champ doit être valorisé', function($context) {
                return in_array($context['data']['type'], ['STRING','TEXT']);
            });
        $validator
            ->scalar('data_date')
            ->datetime('data_date', ['ymd'], 'Date invalide')
            ->notBlank('data_date', 'Ce champ doit être valorisé', function($context) {
                return $context['data']['type'] == 'DATE';
            });
        $validator
            ->scalar('data_numeric')
            ->decimal('data_numeric', null, 'Valeur invalide')
            ->notBlank('data_numeric', 'Ce champ doit être valorisé', function($context) {
                return in_array($context['data']['type'], ['BOOL','INT']);
            });

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
        $rules->add($rules->isUnique(['key']), ['errorField' => 'key', 'message' => 'Cette clé existe déjà']);
        return $rules;
    }


    /**
     * readSettings()
     *
     * @return array  [ key_name => value ]
     */
    public function readSettings()
    {
        $cfg_data = [];
        $db_settings = $this->find()
            ->select(['key_name', 'data','data_date','data_numeric', 'type'])
            ->order(['key_name'])
            ->all();

        if (!empty($db_settings)) {
            foreach($db_settings as $s) {
                $cfg_data = \Cake\Utility\Hash::insert($cfg_data, $s->key_name, $s->value);
            }
        }
        return $cfg_data;
    }


    /**
     * setSettings()
     * Lecture du contenu de la tables settings et MaJ de Configure...
     *
     * @param bool $force
     */
    public function setSettings($force = false) :void
    {

        if (\Cake\Core\Configure::check('ui.CONTROL') && !$force) return;
        if (!$force) {
            \Cake\Core\Configure::restore('cstm_ui', 'default');
            // Ctrl chargement des paramètres "ui"
            if (\Cake\Core\Configure::check('ui.CONTROL') ) return;
        }
/*
        $db_settings = $this->find()
            ->select(['key_name', 'data','data_date','data_numeric', 'type'])
            ->order(['key_name'])
            ->all();

        if (!empty($db_settings)) {
            $cfg_data = [];
            foreach($db_settings as $s) {
                $cfg_data = \Cake\Utility\Hash::insert($cfg_data, $s->key_name, $s->value);
            }
            \Cake\Core\Configure::store('cstm_ui', 'default', ['ui' => $cfg_data]);
            \Cake\Core\Configure::restore('cstm_ui', 'default');
        }
*/
        $cfg_data = $this->readSettings();
        if (count($cfg_data)) {
            \Cake\Core\Configure::store('cstm_ui', 'default', ['ui' => $cfg_data]);
            \Cake\Core\Configure::restore('cstm_ui', 'default');
        }
    }


    /**
     * uiGet()
     *
     * @param array|string $key
     *
     * @return array|false[]|mixed
     */
    public function uiGet($key) {
        if (is_array($key)) {
            $key = join('.', $key);
        }
        if (strpos('ui.', $key) !== 0) {
            $key = 'ui.'.$key;
        }
        return \Cake\Core\Configure::read($key);

    }


}
