<div class="col-m-<?= $field->get('width') ?>">
<?php if ($field->has('label')): ?>
    <?= $this->escape($field->label()) ?>
<?php else: ?>
    <?php $field->get('fields')->render() ?>
<?php endif; ?>
</div>
