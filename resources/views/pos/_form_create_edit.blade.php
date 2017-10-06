        @include('widgets.form._formitem_text', 
                ['name' => 'name', 
                 'title'=>'Name'])
                 
        @include('widgets.form._formitem_text', 
                ['name' => 'aot_name', 
                 'title'=>'AOT name'])
                 
        @include('widgets.form._formitem_text', 
                ['name' => 'universal', 
                 'title'=>'Universal POS tags'])
                 
@include('widgets.form._formitem_btn_submit', ['title' => $submit_title])
