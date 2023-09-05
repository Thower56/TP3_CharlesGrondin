#Dockerfile
FROM httpd:alpine
RUN apk update && apk upgrade
RUN mkdir /srv/htdocs
EXPOSE 80
WORKDIR /usr/local/apache2/conf
COPY ./conf/httpd.conf httpd.conf


