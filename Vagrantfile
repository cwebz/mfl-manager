# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

    config.vm.box = "bento/ubuntu-16.04"
    config.vm.network "private_network", ip: "192.168.10.10"
    config.vm.network :forwarded_port, host: 9200, guest: 9200
    config.vm.network :forwarded_port, host: 5601, guest: 5601
    config.vm.hostname = "mfl-manager.local"
    config.vm.synced_folder ".", "/var/www", :mount_options => ["dmode=777", "fmode=666"]

    config.vm.provision :shell, :path => "provision.sh"

    # Optional NFS. Make sure to remove other synced_folder line too
    #config.vm.synced_folder ".", "/var/www", :nfs => { :mount_options => ["dmode=777","fmode=666"] }

    config.vm.provider "virtualbox" do |v|
        v.memory = 3072
    end

    config.vm.provider "virtualbox" do |v|
        v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
    end

end