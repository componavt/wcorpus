    <!-- JavaScripts -->
    {!!Html::script('js/jquery-3.1.0.min.js')!!}

    <script src="{{ asset('js/app.js') }}"></script>

    @yield('footScriptExtra')

    <script type="text/javascript">
        $(document).ready(function(){
            @yield('jqueryFunc')
        });
    </script>
