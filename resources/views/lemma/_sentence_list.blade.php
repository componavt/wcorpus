<?php $count = 0; ?>
                @if ($list) 
                <h3>Sentences 
                    @if ($limit < sizeof($list))
                    ({{$limit}} from {{sizeof($list)}})
                    @endif
                </h3>
                <OL>
                    <?php foreach ($list as $sentence): 
                         if ($count >= $limit) continue; ?>
                    <LI>{{$sentence['sentence']}} (<b>{{ join(', ',array_values($sentence['wordforms'])) }}</b>)</LI>
                        <?php $count++;
                    endforeach; ?>
                </OL>
                @endif
