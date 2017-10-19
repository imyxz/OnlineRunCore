# 配置cgroup

### 安装cgroup

`sudo apt-get install cgroup-bin cgroup-lite cgroup-tools cgroupfs-mount libcgroup1`

### 更改grub

更改`/etc/default/grub`文件，在配置`GRUB_CMDLINE_LINUX_DEFAULT`中添加`cgroup_enable=memory swapaccount=1`

`sudo update-grub`

### 重新引导

`reboot`

### 创建cgroup控制组(root用户下) 

创建的组名为runner_limit

`cgcreate -g memory,cpu:runner_limit`

限制内存为512M

`echo 536870912 > /sys/fs/cgroup/memory/runner_limit/memory.limit_in_bytes`

禁止swap使用

`echo 0 > /sys/fs/cgroup/memory/runner_limit/memory.swappiness`

转移控制组所有权给用户`runner`

`chown -R runner /sys/fs/cgroup/memory/runner_limit/`

`chown -R runner /sys/fs/cgroup/cpu/runner_limit/`

切换到`runner`用户下，测试是否能执行

`cgexec -g cpu,memory:runner_limit ping 8.8.8.8`

附：内存限制测试代码（1.5G左右）

```c++
#include <iostream>
#include <cstdio>
using namespace std;
int main()
{
    int * n=new int[400000000];
for(int i=0;i<400000000;i++)
	n[i]=i;    
cout<<"Hello World!"<<endl;
	getchar();
}

```

# 配置LXC

### 安装lxc

`sudo apt-get install lxc -y`

### 配置以非超级用户形式运行lxc容器

创建一个用户

`adduser runner`

获取uid和gid范围

`grep runner /etc/subuid`

`grep runner /etc/subgid`

创建配置文件(以runner用户方式登录)

将下文 #UID# 与 #GID# 替换为上面找到的，可能需要根据上面内容更改65536处值

```
mkdir -p ~/.config/lxc
echo "lxc.id_map = u 0 #UID# 65536" > ~/.config/lxc/default.conf
echo "lxc.id_map = g 0 #GID# 65536" >> ~/.config/lxc/default.conf
echo "lxc.network.type = veth" >> ~/.config/lxc/default.conf
echo "lxc.network.link = lxcbr0" >> ~/.config/lxc/default.conf
```

以root权限执行: `echo "runner veth lxcbr0 2" | sudo tee -a /etc/lxc/lxc-u sernet`

### 创建容器 ubuntu16

以`runner`用户登录，*直接使用su切换到runner会有某些问题，因为某些环境变量未更新*

`lxc-create -t download -n runner -- -d ubuntu -r xenial -a amd64`

启动容器

`lxc-start -n runner -d`

接入终端

`lxc-attach -n runner`

安装lxc-init

`apt-get update && apt-get install lxc time -y`

安装编译器

c&c++

`apt-get install gcc g++ -y`

java

`apt-get install default-jdk default-jre -y` 

php7

`apt-get install php7.0-cli -y`

pascal

`apt-get install fp-compiler -y`

python3

`apt-get install python3 -y`

创建代码执行目录

`mkdir /home/runner && chmod 777 /home/runner`

拷贝各种执行脚本到`/etc/`目录

关闭容器

`poweroff`

执行内存是否限制的测试：

` cgexec -g memory:runner_limit lxc-execute -n runner /home/runner/test`

若未显示hello world即成功限制

# 参考资料

```
https://linuxcontainers.org/lxc/getting-started/
https://help.ubuntu.com/lts/serverguide/lxc.html
https://serverfault.com/questions/444232/limit-memory-and-cpu-with-lxc-execute
https://askubuntu.com/questions/836469/install-cgconfig-in-ubuntu-16-04
```