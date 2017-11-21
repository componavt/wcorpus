<div class="row">
    <div class="col-sm-4">Select lemma</div>
    <div class="col-sm-8">        
        @include('widgets.form._formitem_select2',
                ['name' => 'lemma_id',
                 'value' => $lemma_id,
                 'values' => $lemma_values,
                 'class'=>'select-lemma form-control',
                 'is_multiple' => false,
                 'attributes'=>['placeholder' => 'Lemma' ]])
    </div>
</div>
<div class="row">
    <div class="col-sm-1"><b>No</b></div>
    <div class="col-sm-5"><b>Synset</b></div>
    <div class="col-sm-6"><b>Meaning</b></div>
</div>
@if ($action == 'edit')
    @foreach ($synsets as $synset)
        @include('synset._form_edit_synset')
    @endforeach
    <?php $count=0;?>
@endif

{{-- New meaning --}}
<div id='new-meanings'>
    @if ($action == 'create')
        @include('synset._form_create_synset',
                 ['count' => 0,
                  'new_meaning_n' => $new_meaning_n
                 ])
        <?php
            $count = 1;
            $new_meaning_n++;
        ?>
    @endif
</div>
<div class="row">
    <div class="col-sm-6">
        @include('widgets.form._formitem_btn_submit', ['title' => $submit_title])
    </div>
    <div class="col-sm-6" style="text-align:right">
        <button type="button" class="btn btn-success add-new-synset" 
                 data-count='{{ $count }}' data-meaning_n='{{$new_meaning_n}}'>
            Add new synset
        </button>
    </div>
</div>



