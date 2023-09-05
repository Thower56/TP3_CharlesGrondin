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



















             
