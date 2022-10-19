<table class="table table-striped table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <th>Customer ID</th>
            <th>Name</th>
            <th>Reference Code</th>
            <th>Date Added</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
      <?php foreach ($applications as $application): ?>
        <tr>
          <td><?php echo $application['customer_id']; ?></td>
          <td><?php echo $application['full_name']; ?></td>
          <td><?php echo $application['reference_code']; ?></td>
          <td><?php echo $application['date_added']; ?></td>
          <td><button attr-reference-code="<?php echo $application['reference_code']; ?>" type="button" class="btnAssignPartner loading-disabler btn btn-sm btn-primary">Assign</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="text-center">
  <nav aria-label="Page navigation" id="pagination_application"></nav>
</div>
