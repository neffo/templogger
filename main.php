<?php 

require 'config.php';
require 'functions.php';

update_all_sensors();
create_graph("data/28-000008ab7ca8.rrd", "Testing");

?>
