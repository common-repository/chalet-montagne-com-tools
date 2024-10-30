<div class="container">
    <div class="row bs-wizard" style="">


<?php
        $status = 'disabled';
        $wasActive = true;
        foreach($steps as $step => $arrStep){

            if($wasActive)
                $class = 'complete';

            if($arrStep['status'] == 'active') {
                $wasActive = false;
                $class = $arrStep['status'];
            }elseif(!$wasActive){
                $class = $arrStep['status'];
            }
            echo '<div class="col-md-3 bs-wizard-step '.$class.'">
            <div class="text-center bs-wizard-stepnum">';
            esc_html_e( $arrStep['title'] , 'chalet-montagne-private');
            echo '</div>
            <div class="progress"><div class="progress-bar"></div></div>
            <a href="#" class="bs-wizard-dot"></a>
            <div class="bs-wizard-info text-center">';
            esc_html_e( $arrStep['content'] , 'chalet-montagne-private');
            echo'</div>
        </div>';
        }
?>
    </div>
</div>