<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form>
    <input type="hidden" id="filterUserType" name="filterUserType" value="">
    <input type="hidden" id="filterApp" name="filterApp" value="">

    <div class="row wrapper">
        <div class="col-sm-12">
            <h3>Dashboard</h3>
        </div>
    </div>
    <div class="row wrapper">
        <div class="col-sm-12">

            <div class="row">
                <div class="col-sm-12 m-b-sm" >

                    <?php if ($this->session->flashdata('error')): ?>
                   <div class="row">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-6"> 
                           <div class="alert alert-danger alert-dismissible" role="alert">
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <?php echo $this->session->flashdata('error'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                        </div>
                    </div>
                     <?php endif; ?>

                    <div id="hubDaterangePicker">
                        <span class="input-group-addon mobile-only">from</span>
                        <div class="input-daterange input-group full-width">
                            <span class="input-group-addon desktop-only">from</span>
                            <input type="text" class="input-sm form-control" name="start" value="<?php echo $reference_date_start; ?>" data-date-format="<?php echo $this->config->item('mm8_global_date_format'); ?>">
                            <span class="input-group-addon">to</span>
                            <input type="text" class="input-sm form-control" name="end" value="<?php echo $reference_date_end; ?>" data-date-format="<?php echo $this->config->item('mm8_global_date_format'); ?>">
                            <span class="input-group-addon">
                                <span class="presets-addon">
                                    <a class="dropdown-toggle btn btn-xs btn-default" data-toggle="dropdown" href="#">
                                        <i class="fa fa-caret-down"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="javascript:void(0);" onclick="date_range_alltime()">All time</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_today()">Today</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_last7days()">Last 7 Days</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_last30days()">Last 30 Days</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_thismonth()">This Month</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_lastmonth()">Last Month</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_thisyear()">This year</a></li>
                                        <li><a href="javascript:void(0);" onclick="date_range_lastyear()">Last year</a></li>

                                    </ul>
                                </span>
                            </span>
                            <span class="input-group-addon">
                                <span class="actions-addon" style="display:none">
                                    <button id="hubDaterangeApplyBtn" type="button" class="btn btn-primary btn-xs">Apply</button>
                                    <button id="hubDaterangeCancelBtn" type="button" class="btn btn-danger btn-xs">Cancel</button>
                                </span>
                            </span>

                        </div>
                    </div>
                </div>
            </div>

      

            <div class="ibox ibox-rounded float-e-margins">
                <div class="ibox-content">

                    <div class="row m-t-lg">
                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margins white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold countPlateNumberHotlistDetected">0</h2>
                                            <p class="no-margins">Plate Number Hotlist Detected</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margins white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold countPlateNumberWhitelistDetected">0</h2>
                                            <p class="no-margins">Plate Number Whitelist Detected</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margins white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold countPlateNumbers">0</h2>
                                            <p class="no-margins">Plate Numbers</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margin white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold countPIDevices">0</h2>
                                            <p class="no-margins">PI Devices</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>

            <div class="ibox ibox-rounded float-e-margins">
                <div class="ibox-content">

                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" id="toggleSearchBtn" class="btn btn-md btn-link datatable-top-togglers"><i class="fa fa-search"></i> <span class="action-label">Search</span></button>
                    </div><!-- column -->
                </div><!-- row -->

                <div class="row wrapper m-t" id="dtSearchContainer" style="display:none;">
                    <div class="col-sm-8 gray-bg p-md">
                        <div class="col-sm-8 m-b-xs">
                            <input type="text" class="form-control" name="dtSearchText" id="dtSearchText" placeholder="Search">
                        </div>
                        <div class="col-sm-4 m-b-xs">
                            <button type="button" id="dtSearchBtn" class="btn btn-primary search-button">Search</button>
                        </div>
                    </div><!-- column -->
                </div><!-- row -->

                <table id="dtPlateNumbersTbl" class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Date Added</th>
                            <th width="20%"></th>
                            <th>Plate Number</th>
                            <th>Tracking Type</th>
                            <th>PI Device</th>
                            <th>Location</th>
                            <th>Comments</th>
                            <th>SMS Notified</th>
                            <th style="max-width: 100px !important; min-width: 50px !important;">Actions</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>

            <div class="ibox ibox-rounded float-e-margins">
                <div class="ibox-content">

                    <div class="row m-t-lg m-b-lg">
                        <div class="col-sm-3" id="pieChartWrapper"></div>
                        
                        <div class="col-sm-9" id="barChartWrapper"></div>
                    </div>
                   
                </div>
            </div>

            <br><br><br>

        </div>
    </div>
</form>