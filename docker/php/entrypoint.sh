#!/bin/sh

uid=$(stat -c %u /var/www/html)
gid=$(stat -c %g /var/www/html)

if [ "$(id -u)" -eq 0 ] && [ "$(id -g)" -eq 0 ]; then
    if [ $# -eq 0 ]; then
        php-fpm --allow-to-run-as-root
    else
        exec "$@"
    fi
fi

foo_user="foo"
bar_group="bar"

sed -i -E "s/$foo_user:x:[0-9]+:[0-9]+:/$foo_user:x:$uid:$gid:/g" /etc/passwd
sed -i -E "s/$bar_group:x:[0-9]+:/$bar_group:x:$gid:/g" /etc/group

sed -i "s/user = www-data/user = $foo_user/g" /usr/local/etc/php-fpm.d/www.conf
sed -i "s/group = www-data/group = $bar_group/g" /usr/local/etc/php-fpm.d/www.conf

user=$(id -un)
if [ $# -eq 0 ]; then
    php-fpm
else
    gosu "$user" "$@"
fi
