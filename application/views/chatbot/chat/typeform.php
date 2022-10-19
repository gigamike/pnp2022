<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="typeformForm">
    <input type="hidden" name="typeformSessionCode" id="typeformSessionCode" value="<?php echo $typeformSession->u_code; ?>">
    <input type="hidden" name="currentState" id="currentState" value="<?php echo $typeformSession->state; ?>">
    <input type="hidden" name="rowCount" id="rowCount" value="0">

    <section class="container m-t-lg m-b-sm">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                <div style="height:400px">
                    <div id="typeformContainerDiv" class="full-height-scroll"></div>
                </div>
            </div>
        </div>
    </section>
</form>