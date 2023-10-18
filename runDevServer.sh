#/bin/sh
# lancement serveur de dev symfony (en root sinon erreur message "no left space on device" sous Linux)
# le bin symfony s'installe avec le package symfony-cli https://symfony.com/download
echo "***********************************"
echo "* Lancement serveur dev symfony ***"
echo "* Url : http://localhost:8081/ ****"
echo "***********************************"

sudo symfony server:start --port=8081 --dir=/home/vmaury/NetBeansProjects/Dolilarbre-bolt
