#!/usr/bin/python

import rrdtool

databaseFile = "temps.rrd"
MIN_TEMP = -50
ERROR_TEMP = -999.99

rrds_to_filename = {
  "outside" : "/sys/bus/w1/devices/10-0008021eb5ed/w1_slave",
  "inside" : "/sys/bus/w1/devices/10-0008021eb67a/w1_slave",
  "crawlspace" : "/sys/bus/w1/devices/10-0008021ec8b6/w1_slave",
}

def read_temperature(file):
  tfile = open(file)
  text = tfile.read()
  tfile.close()
  lines = text.split("\n")
  if lines[0].find("YES") > 0:
    temp = float((lines[1].split(" ")[9])[2:])  # (get rid of the t=)
    temp /= 1000
    return temp
  return ERROR_TEMP

def read_all():
  template = ""
  update = "N:"
  for rrd in rrds_to_filename:
    template += "%s:" % rrd
    temp = read_temperature(rrds_to_filename[rrd])
    update += "%f:" % temp
  update = update[:-1]
  template = template[:-1]
  rrdtool.update(databaseFile, "--template", template, update)

read_all()
