<div class="grid" style="float: left;width:728px;">
@foreach($data as $item)
    @element('cell', $item, ['property_map' => ['name' => 'title']])
@endforeach
</div>
<div class="clearfix"></div>
