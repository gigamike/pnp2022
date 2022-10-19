<?php

defined('BASEPATH') or exit('No direct script access allowed');

// if this menu items is being accessed through other roles (workspace admin / referring agent)
// then touchbase first, before going to manager page

echo '<li' . ($user_menu === "dashboard" ? ' class="active"' : '') . '><a href="' . base_url() . "dashboard" . '"><span class="nav-label">Dashboard</span></a></li>';

echo '<li' . ($user_menu === "plate-numbers" ? ' class="active"' : '') . '><a href="' . base_url() . "plate-numbers" . '"><span class="nav-label">Plate Numbers</span></a></li>';

if ($this->session->utilihub_hub_user_role == 1) {
    echo '<li' . ($user_menu === "users" ? ' class="active"' : '') . '><a href="' . base_url() . "users" . '"><span class="nav-label">Users</span></a></li>';
}

if ($this->session->utilihub_hub_user_role == 1) {
    echo '<li' . ($user_menu === "pi-devices" ? ' class="active"' : '') . '><a href="' . base_url() . "pi-devices" . '"><span class="nav-label">PI Devices/Inverntory</span></a></li>';
}

if ($this->session->utilihub_hub_user_role == 1) {
    echo '<li' . ($user_menu === "settings" ? ' class="active"' : '') . '><a href=""><span class="nav-label">Settings</span></a></li>';
}
