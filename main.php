<?php 

require 'config.php';
require 'functions.php';


update_all_sensors();
create_graph(array("/home/pi/public_html/templogger/data/28-000008ab7ca8.rrd", "/home/pi/public_html/templogger/data/28-03168b079bff.rrd"), array("Room", "Beer"),  "Data Logger Testing");

?>
