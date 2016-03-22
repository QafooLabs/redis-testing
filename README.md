# Redis Cluster Testing

Different tests to verify Redis Cluster functionality in terms of distributed systems theory:

- CAP Theorem tests for consistency and availability during network partitions
- session blocking tests

## Usage

Setup a cluster of 3-4 nodes, for example on Digitalocean and create an Ansible inventory file

    redis-test01 ansible_ssh_user=root ansible_ssh_host=IP1
    redis-test02 ansible_ssh_user=root ansible_ssh_host=IP2
    redis-test03 ansible_ssh_user=root ansible_ssh_host=IP3
    redis-test04 ansible_ssh_user=root ansible_ssh_host=IP4

    [php]
    redis-test04

    [redis]
    redis-test01
    redis-test02
    redis-test03

    [partition_a]
    redis-test01
    redis-test02

    [partition_b]
    redis-test03

    [all:vars]
    redis_server_end_port=7001

This creates a cluster with 3 redis nodes having 2 servers each and one web
frontend server. The ansible groups `[partition_a/b]` control how iptables
is used to split different servers from each other.

Provision this setup with:

    $ ansible-playbook -i servers_inventory provision.yml

To make the cluster running, connect to all of the Redis nodes and call:

    $ /root/redis-cluster start

To start all the servers on that node.

And then on one node create the cluster with `redis-trib` using:

    $ /root/create-cluster.sh

You can use the file `scripts/generate_user_urls.php`
to pass the ip/url to the web node and it will generate lists of
urls to use with the tool `siege`. The traffic is designed
such that it will hit all the shards of the cluster.

Generating load:

    $ siege -f cluster_set.txt -c 100

You can monitor the cluster health by calling one of the following commands:

    $ /var/www/redis-trib.rb info <HOST>:<PORT>
    $ redis-cli -h <HOST> -p <PORT> cluster slots
    $ redis-cli -h <HOST> -p <PORT> cluster nodes

## Configuration

You can modify the different config files in `templates/` folder
and reprovision the servers.

- `cluster.conf.j2` contains redis cluster related configuration
- `redis.conf.j2` contains redis server related configuration

## Injecting faults

### Network partition

You can partition the network with Ansible:

    $ ansible-playbook -i servers_inventory partition.yml

You can heal the partition with:

    $ ansible-playbook -i servers_inventory heal.yml

### Kill Redis Node

Use `redis-cli` tool on one of the Redis nodes to call:

    $ redis-cli -h IPADDRSES -p 7000 debug segfault

This forces a node to segfault and shows how the cluster automatically
fails over. You can observe the faults happening during this process
looking at the siege output.

### Manual failover

Use `redis-cli` tool on one of the Redis nodes to call
for a slave:

    $ redis-cli -h IPADDRSES -p 7000 cluster failover
