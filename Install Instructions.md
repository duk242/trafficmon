Ducky's Traffic Monitoring Script Thing.

To install:
Copy updateTraffic and wrtbwmon to /config/scripts/
Copy trafficMonitorSetup to /config/scripts/post-config.d
Chmod them all to be accessible.

```
chmod +x /config/scripts/wrtbwmon; chmod +x /config/scripts/updateTraffic; chmod +x /config/scripts/post-config.d/trafficMonitorSetup
```

To set up each machine, edit/paste this line (Lowercase MAC Address):
echo "ma:ca:dd:re:ss:00,ComputerName" >> /config/scripts/users.txt

Set up the cron jobs to make it run every 10 minutes

If you have on/off peak, do this one:

```
configure

set system task-scheduler task peakTraffic2 crontab-spec "*/10,59 8-23 * * *"
set system task-scheduler task peakTraffic2 executable path /config/scripts/updateTraffic
set system task-scheduler task peakTraffic2 executable arguments "peak"

set system task-scheduler task peakTraffic3 crontab-spec "*/10,59 3-7 * * *"
set system task-scheduler task peakTraffic3 executable path /config/scripts/updateTraffic
set system task-scheduler task peakTraffic3 executable arguments "offpeak"

set system task-scheduler task peakTraffic4 crontab-spec "*/10,59 0-2 * * *"
set system task-scheduler task peakTraffic4 executable path /config/scripts/updateTraffic
set system task-scheduler task peakTraffic4 executable arguments "peak"

set system offload ipv4 vlan disable
set system offload ipv4 forwarding disable

commit
save
exit
```

If you only have on peak traffic do this one:

```
configure

set system task-scheduler task peakTraffic2 crontab-spec "*/10 * * * *"
set system task-scheduler task peakTraffic2 executable path /config/scripts/updateTraffic
set system task-scheduler task peakTraffic2 executable arguments "peak"

set system offload ipv4 vlan disable
set system offload ipv4 forwarding disable

commit
save
exit
```

To access your traffic usage once you've done the above steps, go to:  http://routerIP/tests/usage.htm

For a cooler version, put index.php in /var/www/htdocs/tests/