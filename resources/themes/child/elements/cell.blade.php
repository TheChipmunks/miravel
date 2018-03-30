<div style="float:left;padding:3px;height:100px;overflow:hidden;width:{{ floor(100/$element->getOption('perline', 3)) }}%">
    <div style="border:1px solid grey;height:100%;text-align:center;position:relative;">
        <div class="cell-content" style="position: absolute; top:50%; transform: translateY(-50%); text-align:center; width: 100%">
            @prop('data.name')
        </div>
    </div>
</div>
