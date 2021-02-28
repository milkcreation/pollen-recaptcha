<?php
/**
 * @var Pollen\Field\FieldViewLoaderInterface $this
 */
?>
<?php $this->before(); ?>
<?php echo $this->partial('tag', [
    'tag'   => 'div',
    'attrs' => $this->get('attrs', [])
]);
?>
<?php $this->after();