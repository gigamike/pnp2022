<?php

defined('BASEPATH') or exit('No direct script access allowed');

echo '<a class="btn btn-xs btn-primary btn-w-xs m-r-xs" href="' . base_url() . 'dashboard/view/' . $log_id . '">View</a>';
echo '<a class="btn btn-xs btn-danger btn-w-xs m-r-xs" href="javascript:;" onclick="delete_to(\'' . $log_id . '\')">Remove</a>';
