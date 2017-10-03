#!/bin/bash
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games
export LANG=zh_CN.UTF-8
export LC_ALL=zh_CN.UTF-8
env LC_ALL=zh_CN.UTF-8
mv /home/runner/source.code /home/runner/Main.java
cd /home/runner
if javac Main.java 2>>/home/runner/compile_error.txt ; then
    echo 0 > /home/runner/compile.state
    /bin/chmod -R 777 /home/runner/
    cd /home/runner/
    /usr/bin/time -v -o /home/runner/time.txt /usr/bin/sudo -u nobody /usr/bin/java -Xms32m -Xmx256m Main 1 > /home/runner/output.txt < /home/runner/input.txt 2>>/home/runner/error.txt
    echo 0 > /home/runner/run.state
else
    echo 1 > /home/runner/compile.state
    echo 1 > /home/runner/run.state
fi
