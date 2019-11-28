kill $(ps aux | grep '[w]eblate.uwsgi' | awk '{print $2}')
bash ./start.sh
