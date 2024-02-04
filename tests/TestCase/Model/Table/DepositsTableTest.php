<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DepositsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DepositsTable Test Case
 */
class DepositsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DepositsTable
     */
    protected $Deposits;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Deposits',
        'app.Creators',
        'app.Items',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Deposits') ? [] : ['className' => DepositsTable::class];
        $this->Deposits = $this->getTableLocator()->get('Deposits', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Deposits);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
