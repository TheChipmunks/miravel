<div class="grid" style="float: left;width:728px;">
@foreach($element->getData() as $item)
    @element('cell', $item, ['property_map' => ['name' => 'title']])
@endforeach
</div>
<div class="clearfix"></div>
