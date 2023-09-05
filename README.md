# TP3_CharlesGrondin

## Preparation
Dans un dossier, vous aurez des dossiers et fichiers dans cette configuration :

```
├── README.md
├── www.tp3.com
   ├── docker-compose.yml
   ├── httpd.conf
   ├── logs.txt
   ├── nginx
   │   └── default.conf
   ├── php
   │   └── Dockerfile
   ├── php2
   │   └── Dockerfile
   ├── serveur1
   │   ├── Dockerfile
   │   ├── conf
   │   │   └── httpd.conf
   │   └── html
   │       └── index.php
   └── serveur2
       ├── Dockerfile
       ├── conf
       │   └── httpd.conf
       └── html
           └── index.php
```
nous allons maintenant passer sur chaqu'un de fichier pour preparer la configuration.

## Commande
- ```docker run -dit --name my-apache-app -p 8080:80``` pour faire une image temporaire a nos besoin
- ```sudo docker cp container-id:/chemin/ /destination/```
- ```docker compose up --build -d``` pour construire nos services
- ```docker compose down --rmi local -v``` pour fermer et nettoyer nos docker
- ```docker compose ps``` pour voir les services ouvert
- ```docker compose logs``` voir s'il y a des problemes avec les services
- ```docker compose logs | grep ####``` pour filtrer les services

## Host

Bien aller ajouter www.tp3.com et tp3.com a votre fichier host

## Docker-compose

Dans notre Docker-compose nous allons creer:
- Un proxy
- 2 serveur web
- 2 serveur php
- Une base de donner MariaDB
- 2 network, frontend et Backend
- et un volume pour la base de donne

Avec notre configuration, nginx va gerer les charges entre les serveurs web et nous verrons comment les connections sont faites.

```
version: '3'
services:
  proxy:
    image: 'nginx:alpine'
    ports:
      - '80:80'
    networks:
      - frontend
    depends_on:
      - php
      - php2
    volumes:
      - './nginx/default.conf:/etc/nginx/conf.d/default.conf:ro'
  serveur1:
    build: './serveur1/'
    networks:
      - frontend
      - backend1
    depends_on:
      - php
      - mariadb
    volumes:
      - './serveur1/html:/srv/htdocs'
  serveur2:
    build: './serveur2/'
    networks:
      - frontend
      - backend2
    depends_on:
      - php2
      - mariadb
    volumes:
      - './serveur2/html:/srv/htdocs'
  php:
    networks:
      - backend1
    volumes:
      - './serveur1/html:/srv/htdocs'
    build: './php/'
  php2:
    networks:
      - backend2
    volumes:
      - './serveur2/html:/srv/htdocs'
    build: './php2/'
  mariadb:
    image: mariadb
    restart: always
    networks:
      - backend1
      - backend2
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
    volumes:
      - dbdata:/var/lib/mysql
networks:
  frontend:
  backend1:
  backend2:
volumes:
  dbdata:
```

### Les services
- Nginx, il est ecoute le port 80, il depend de php pour sont bon fonctionnement avec les serveur web, il est sur le network frontend.
- Serveur1 et Serveur2, contruit par nos DockerFile, ils dependents de php et mariaDB, ils sont sur les deux network pour l'index et la base de donne.
- Php et Php, deux services pour offrir au deux serveur web les fonctionnalites de php, ils sont sur le reseau backend pour la connection avec mariaDB.
- MariDB, la base de donne pour les 2 serveur web.
- Network, pour connecter les services avec un reseau interne.
- DbData, pour avoir un volume persistant MariaDB.



## Nginx
Dans un fichier texte, nous allons ecrire quelque ligne de configuration pour preparer nginx a devenir notre proxy.

### Configuration
Pour notre proxy nginx notre configuration resemblera a ca:

```
upstream monsite-servers {
   	server serveur1 max_fails=2;
    	server serveur2 max_fails=2;
    }

server {
    listen 80;
    server_name tp3.com www.tp3.com;

    index index.php;

    location / {
        proxy_pass         http://monsite-servers;
        proxy_redirect     off;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Host $server_name;

    }
```

#### Balancer la charge
```
upstream monsite-servers {
   	server web1 max_fails=2; # Maximum de tentative 2
    server web2 max_fails=2;# Maximum de tentative 2
    }
```
Cette partie du code indique au proxy quel serveur il doit travailler avec pour balancer la charge de travail

```
listen 80;
    server_name tp3.com www.tp3.com;

    index index.php;
```
- ici on lui indique quel port ecouter pour le serveur
- on lui donne le nom du serveur
- on change l'index pour index.php
  
```
location / {
        proxy_pass         http://monsite-servers;
        proxy_redirect     off;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Host $server_name;
    }
```
Ici on donne comme indication a Nginx de passer en 'upstream' les deux serveurs
cest avec ca qui va redistribuer la charge.

## Dockerfiles

nous aurrons 2 sortent de Dockerfiles, un pour les serveur web et pour php

#### Pour php

```
# Pour image de php
FROM php:fpm-alpine
# Utilise une petite image

RUN apk update; \
    apk upgrade;
# Met a jour le serveur

# Install mysqli
RUN docker-php-ext-install mysqli
```

Le dockerfile est la pour installer et mettre a jour php sur les serveurs web

#### Pour les serveurs Web

```
#Dockerfile
FROM httpd:alpine
RUN apk update && apk upgrade
RUN mkdir /srv/htdocs
EXPOSE 80
WORKDIR /usr/local/apache2/conf
COPY ./conf/httpd.conf httpd.conf
```

Pour les serveurs, question de garder le controle sur question qui ce passe dans nos serveur web
nous allons mettre a jours les serveurs web, faire un dossier pour y mettre nos page et copier notre
configuration personnel


## Httpd.conf

Faite : ```$ docker run -dit --name my-apache-app -p 8080:80 ```
nous allons chercher une copie de fichier de configuration

Avec la commande:
```
sudo docker cp container-id:/etc/httpd/conf/httpd.conf .
```
vous allez copiez le fichier
de configuration pour le personaliser a nos besoin

nous allons modifier les lignes suivante:

Modifier les Directory et mettre 
```/srv/htdocs```
Au lieu du chemin par defaut.

- Avant les commentaires de DocumentRoot ajourter --> ProxyPassMatch "^/(.*\.php(/.*)?)$" "fcgi://php:9000/srv/htdocs/$1" (Mettre php2:9000 pour le serveur2)
- # Enlever le commentaire devant des lignes suivantes:
LoadModule deflate_module modules/mod_deflate.so
LoadModule xml2enc_module modules/mod_xml2enc.so
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so
- Sous <Directory "/srv/htdocs"> modifier AllowOverride
AllowOverride All

## HTML

Dans le dossier html ajouter un index.php avec :

```
<html>
	<title>Tp3 page #</title>
	<h1>Mon site nginx numero # :3 !!</h1>

	<?php 
	$host = 'mariadb';
	$user = 'root';
	$pass = 'rootpassword';
	$conn = new mysqli($host, $user, $pass);

	if ($conn->connect_error) {
		die("La connexion a échoué: " . $conn->connect_error);
	} 
	echo "Connexion réussie à MariaDB!";
	?>


</html>

```
La page va nous aider a confirmer que php et la base de donnee fonctionne


## Demarrer les services

Avec la commande ```docker compose up --build -d```
si tout est correct en vous devriez avoir deux fenetre comme ca :

![image](https://github.com/Thower56/TP3_CharlesGrondin/assets/112575794/dd556dc9-33ff-43f4-b694-00eba6243007)

Vous pouvez tout fermer avec ```docker compose down --rmi local -v```
verifier que tout est fermer avec ```docker compose ps```













             
