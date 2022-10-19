<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">#map {
  height: 400px;
  /* The height is 400 pixels */
  width: 100%;
  /* The width is the width of the web page */
}</style>
    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "dashboard"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Plate Number Log
            </a>
        </div>
        <div class="col-sm-12">
            <h3><?php echo $plate_number->plate_number; ?></h3>
        </div>
    </div>
    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">
            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>
            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">

                    <div class="row">
                        <div class="col-sm-4">
                            <h3>Image Captured</h3>
                            <img class="img-thumbnail" src="<?php echo $plate_number_log->img_url; ?>">

                            <br>
                            <br>
                            <h3>Map</h3>
                            <div id="map"></div>
                            <br>
                            <br>
                        </div>
                        <div class="col-sm-8">
                            <strong>Datetime: </strong> <?php echo $plate_number->date_added; ?><br>
                            <strong>Plate Number: </strong> <?php echo $plate_number->plate_number; ?><br>
                            <strong>Comments: </strong> <?php echo $plate_number->comments; ?><br>

                            <br>
                            <br>
                            <h3>Tracking History</h3>
                            <table class="table table-responsive">
                                <thead>
                                    <tr>
                                        <th>Date Tracked</th>
                                        <th>Track Type</th>
                                        <th>PI Device</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($tracks) > 0): ?>
                                        <?php foreach ($tracks as $track): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y h:i:s A', strtotime($track->date_added)); ?></td>
                                        <td><?php echo $track->tracking_type; ?></td>
                                        <td><?php echo $track->u_code; ?></td>
                                        <td><?php echo $track->location;?></td>
                                    </tr>    
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <br>
                            <br>
                            <h3>SMS Notification History</h3>
                            <table class="table table-responsive">
                                <thead>
                                    <tr>
                                        <th>Date Sent</th>
                                        <th>Tracking Type</th>
                                        <th>Mobile Phone</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($sms) > 0): ?>
                                        <?php foreach ($sms as $row): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y h:i:s A', strtotime($row->date_added)); ?></td>
                                        <td><?php echo $row->tracking_type; ?></td>
                                        <td><?php echo $row->mobile_phone; ?></td>
                                        <td><?php echo $row->first_name;?></td>
                                        <td><?php echo $row->last_name;?></td>
                                        <td><?php echo $row->message;?></td>
                                    </tr>    
                                        <?php endforeach; ?>
                                    <?php endif; ?>   
                                </tbody>
                            </table>

                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

