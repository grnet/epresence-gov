**Supervisor configurations:**

laravel-worker.conf:

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/opt/plesk/php/7.2/bin/php /var/www/vhosts/epresence.gr/httpdocs/artisan queue:work --sleep=3 --tries=3 --queue=high,low,default --delay=5 --timeout=45
autostart=true
autorestart=true
user=epresence
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/vhosts/epresence.gr/httpdocs/storage/logs/worker.log


node-worker.conf:

[program:laravel-node-worker]
process_name=%(program_name)s_%(process_num)02d
directory=/var/www/vhosts/epresence.gr/httpdocs
command=/opt/plesk/node/12/bin/node /opt/plesk/node/12/bin/laravel-echo-server start
autostart=true
autorestart=true
user=epresence
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/vhosts/epresence.gr/httpdocs/storage/logs/node-worker.l$

