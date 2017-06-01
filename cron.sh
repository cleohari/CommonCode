#!/bin/sh
if [ -f '/etc/redhat-release' ]; then
  export USERNAME='apache'
elif python -mplatform | grep -qi Ubuntu; then
  export USERNAME='www-data'	
fi

if [ ! -d /var/php_cache/browser ]; then
  sudo mkdir -p /var/php_cache/browser
  sudo chown $USERNAME:$USERNAME /var/php_cache/browser
fi	

sudo -u $USERNAME php -d memory_limit=-1 cron.php
if [ -f '/etc/redhat-release' ]; then
  sudo chcon -Rv --type=httpd_sys_content_t /var/php_cache/browser
fi
