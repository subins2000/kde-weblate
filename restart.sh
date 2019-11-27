kill $(ps aux | grep '[w]eblate runserver' | awk '{print $2}')
bash ./start.sh
