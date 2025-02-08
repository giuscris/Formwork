<fieldset <?= $this->attr([
    'id'       => $field->name(),
    'class'    => 'toggle-group',
    'disabled' => $field->isDisabled()
]) ?>>
<?php foreach ((array) $field->get('options') as $value => $label): ?>
    <label>
        <input <?= $this->attr([
            'type'    => 'radio',
            'name'    => $field->formName(),
            'value'   => $value,
            'checked' => $value == $field->value()
        ]) ?>>
        <span><?= $this->escape($label) ?></span>
    </label>
<?php endforeach; ?>
</fieldset>
