<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
				include_once(APPPATH.'views/admin/invoices/recurring/filter_params.php');
				$this->load->view('admin/invoices/recurring/list_template');
			?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [4,5,6];</script>
<?php init_tail(); ?>
<script>
	$(function(){
		init_invoice();
	});
</script>
</body>
</html>
