<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
</div>
<!-- wrapper end -->

<!-- Spinner Modal-->
<div class="modal inmodal" id="spinnerModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm text-center" style="margin-top:200px;"><i class="fa fa-refresh fa-spin fa-5x text-white"></i></div>
</div>

<!-- wrapper end -->
<div data-iframe-height></div>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/plugins/validate/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/plugins/wow/wow.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/chatbot/core.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/chatbot/widgets/iframe-resizer/iframeResizer.contentWindow.min.js"></script>
<?php foreach ($scripts as $js) {
    echo "<script type=\"text/javascript\" src=\"" . $js . "\"></script>\n";
}
?>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/inspinia.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/plugins/pace/pace.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/plugins/sweetalert/sweetalert.min.js"></script>

</body>
</html>
