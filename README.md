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























             
