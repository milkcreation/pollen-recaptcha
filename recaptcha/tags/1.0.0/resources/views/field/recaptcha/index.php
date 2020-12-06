<?php
/**
 * @var tiFy\Field\FieldView $this
 */
?>
<?php $this->before(); ?>
<?php echo partial('tag', [
    'tag'   => 'div',
    'attrs' => $this->get('attrs', [])
]);
?>
<?php $this->after();