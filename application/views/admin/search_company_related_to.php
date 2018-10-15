<ul class="dropdown-menu search-results animated fadeIn display-block">
    <?php
    $total = 0;

    foreach($result as $data){
     if(count($data['result']) > 0){
         $total++;
         ?>
         <li role="separator" class="divider"></li>
         <li class="dropdown-header"><?php echo $data['search_heading']; ?></li>
         <?php } ?>
         <?php foreach($data['result'] as $_result){
            $output = '';
            switch($data['type']){
                case 'companies':
                $output = '<a href="javascript:set_company_related_to_tag(\''.$tag. '\',\''.$_result['userid']. '\',\''.$_result['company'].'\')">'.$_result['company'].'</a>';
                break;
            }
            ?>
            <li><?php echo $output; ?></li>
            <?php } ?>
            <?php } ?>
            <?php if($total == 0){ ?>
                <li class="padding-5 text-center"><?php echo _l('not_results_found'); ?></li>
                <?php } ?>
            </ul>
