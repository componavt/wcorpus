<?php $count = 0; ?>
                @if ($list) 
                <h3>Context lemmas
                    @if ($limit < sizeof($list))
                    <br>{{$limit}} from {{sizeof($list)}}
                    @else 
                    ({{sizeof($list)}})
                    @endif
                </h3>
                <table class="table table-striped">
                    <tr><th>Lemma</th><th>Freq1</th><th>Freq2</th></tr>
                    <?php foreach ($list as $lemma_id => $info): 
                         if ($count >= $limit) continue; ?>
                    <tr><td>{{$info['lemma']}}</td><td>{{$info['freq1']}}</td><td>{{$info['freq2']}}</td></tr>
                        <?php $count++;
                    endforeach; ?>
                </table>
                @endif
                
