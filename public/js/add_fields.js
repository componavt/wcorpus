function addSynset() {
    $(".add-new-synset").click(function(){
        var count = $(this).data("count");
        var meaning_n = $(this).data('meaning_n');
        $.ajax({
            url: '/synset/synset/create/', 
            data: {count: count, meaning_n: meaning_n},
            type: 'GET',
            success: function(result){
                $("#new-meanings").append(result);
            }
        }); 
        $(this).data('count',count + 1);
        $(this).data('meaning_n', meaning_n+1);
    });    
}