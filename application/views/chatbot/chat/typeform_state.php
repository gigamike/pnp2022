<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="state<?php echo $rowCount; ?>Div" class="widget white-bg" attr-state="<?php echo $currentState; ?>" style="height:400px">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-sm-12 m-t-sm m-b-sm typeform">
            <?php
                $this->load->view("chatbot/chat/typeform_state_" . $currentState);
            ?>
        </div>
    </div>
</div>