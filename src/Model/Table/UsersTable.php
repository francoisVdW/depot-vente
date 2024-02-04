<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('id');
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
            ->scalar('username')
            ->maxLength('username', 20)
            ->notBlank('username', 'Le nom de connexion est obligatoire');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 30, 'Le nom est trop long (30 car. max)')
            ->notBlank('last_name', 'Le nom est obligatoire');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 20, 'Le prénom est trop long (20 car. max)')
            ->allowEmptyString('first_name');

        $validator
            ->scalar('password')
            ->requirePresence('password', 'create', 'Ce champ est requis')
            ->notEmptyString('password', 'Ce champ doit être valorisé');
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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username', 'message' => 'Ce nom de connexion existe déjà']);

        return $rules;
    }


    /**
     * listAvailableCashFundUsers()
     * Liste des Users qui on accès aux fonctionnalités "cashRegister"
     * ET qui ne sont pas connecté à une caisse (ouverte ou en activité)
     *
     * @return array
     */
    public function listAvailableCashFundUsers()
    {
        $users = $this->find()
            ->select(['id', 'first_name', 'last_name'])
            ->where('(roles_json LIKE \'%MNGR%\' OR roles_json LIKE \'%ADMIN%\' OR roles_json LIKE \'%C-REGIST%\')
            AND NOT EXISTS (Select 1 From cash_registers As cr Where cr.user_id = Users.id And cr.state In (\'OPEN\', \'SALE IN PROGRESS\') )')
            ->order(['last_name', 'first_name'])
            ->all();
        $list_users = [];
        if ($users) foreach($users as $user) $list_users[$user->id] = $user->title;
        return $list_users;
    }


    /**
     * beforeSave
     *
     * @param \Cake\Event\Event $event
     * @param $entity
     * @param array $options
     * @return bool
     */
    public function beforeSave(\Cake\Event\Event $event, $entity, $options = [])
    {
        if (!empty($entity->last_name)) {
            $entity->last_name = strtoupper($entity->last_name);
        }
        return true;
    }

}
