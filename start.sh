# Run in bg
nohup bash ./run.sh &> run.log &
tail -f run.log
