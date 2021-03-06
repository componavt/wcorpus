<div class="row">
    <div class="col-sm-1">        
        @include('widgets.form._formitem_text', 
                ['name' => 'synsets['.$synset->id.'][meaning_n]',
                 'value'=> $synset->meaning_n,
                 'attributes'=>['size' => 2]])
    </div>
    <div class="col-sm-5">        
        @include('widgets.form._formitem_text', 
                ['name' => 'synsets['.$synset->id.'][synset]',
                 'value' => $synset->synset])
    </div> 
    <div class="col-sm-6">        
        @include('widgets.form._formitem_text', 
                ['name' => 'synsets['.$synset->id.'][meaning_text]',
                 'value' => $synset->meaning_text])
    </div> 
</div>
