# Templogger

Simple rrdtool-based php scripts to log temperatures from DS18B20 sensors on
the Raspberry Pi (should work on all revisions). It will automatically
create rrd files for any sensors it finds. It will also

Requirements:
- Raspiberry Pi
- DB18B20 sensor(s) wired up right
- Apache2 + PHP + php-cli
- RRDtool

Install:
- create public_html folder in /home/pi
- enable mod userdir in apache2
- Run:
cd ~/public_html
git clone git@github.com:neffo/templogger.git
- add php /home/pi/public_html/templogger/main.php to crontab (at least
every 5 minutes)

