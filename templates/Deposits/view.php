<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Deposit $deposit
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Deposit'), ['action' => 'edit', $deposit->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Deposit'), ['action' => 'delete', $deposit->id], ['confirm' => __('Are you sure you want to delete # {0}?', $deposit->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Deposits'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Deposit'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="deposits view content">
            <h3><?= h($deposit->title) ?></h3>
            <table>
                <tr>
                    <th><?= __('Title') ?></th>
                    <td><?= h($deposit->title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Phone') ?></th>
                    <td><?= h($deposit->phone) ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($deposit->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Creator Title') ?></th>
                    <td><?= h($deposit->creator_title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($deposit->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Creator Id') ?></th>
                    <td><?= $this->Number->format($deposit->creator_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($deposit->created) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Items') ?></h4>
                <?php if (!empty($deposit->items)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Bar Code') ?></th>
                            <th><?= __('Mfr Part No') ?></th>
                            <th><?= __('Deposit Id') ?></th>
                            <th><?= __('Requested Price') ?></th>
                            <th><?= __('Sale Price') ?></th>
                            <th><?= __('Happy Hour') ?></th>
                            <th><?= __('Sale Id') ?></th>
                            <th><?= __('Return Date') ?></th>
                            <th><?= __('Return Cause') ?></th>
                            <th><?= __('Created') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($deposit->items as $items) : ?>
                        <tr>
                            <td><?= h($items->id) ?></td>
                            <td><?= h($items->name) ?></td>
                            <td><?= h($items->bar_code) ?></td>
                            <td><?= h($items->mfr_part_no) ?></td>
                            <td><?= h($items->deposit_id) ?></td>
                            <td><?= h($items->requested_price) ?></td>
                            <td><?= h($items->sale_price) ?></td>
                            <td><?= h($items->happy_hour) ?></td>
                            <td><?= h($items->sale_id) ?></td>
                            <td><?= h($items->return_date) ?></td>
                            <td><?= h($items->return_cause) ?></td>
                            <td><?= h($items->created) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Items', 'action' => 'view', $items->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Items', 'action' => 'edit', $items->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Items', 'action' => 'delete', $items->id], ['confirm' => __('Are you sure you want to delete # {0}?', $items->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
