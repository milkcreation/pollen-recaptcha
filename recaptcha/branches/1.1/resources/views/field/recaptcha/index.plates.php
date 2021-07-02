<?php
/**
 * @var Pollen\Field\FieldTemplateInterface $this
 */
?>
<?php $this->before(); ?>
<?php echo $this->partial('tag', [
    'tag'   => 'div',
    'attrs' => $this->get('attrs', [])
]);
?>
<?php $this->after();