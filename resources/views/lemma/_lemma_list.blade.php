<?php $count = 0; ?>
                @if ($list) 
                <h3>Context lemmas
                    @if ($limit < sizeof($list))
                    <br>{{$limit}} from {{sizeof($list)}}
                    @endif
                </h3>
                <table>
                    <?php foreach ($list as $lemma_id => $freq): 
                         if ($count >= $limit) continue; ?>
                    <tr><td>{{$lemma_strings[$lemma_id]}}</td><td>{{$freq}}</td></tr>
                        <?php $count++;
                    endforeach; ?>
                </table>
                @endif
                
